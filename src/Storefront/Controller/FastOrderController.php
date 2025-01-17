<?php declare(strict_types=1);

namespace FastOrder\Storefront\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Uuid\Uuid;
use FastOrder\Service\FastOrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\Routing\Attribute\RouteScope;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;


#[Route(defaults:["_routeScope"=>["storefront"]])]
class FastOrderController extends StorefrontController
{
    private CartService $cartService;
    private EntityRepository $productRepository;
    private \Twig\Environment $twig;
    private FastOrderService $fastOrderService;

    public function __construct(CartService $cartService, EntityRepository $productRepository, \Twig\Environment $twig, FastOrderService $fastOrderService)
    {
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->twig = $twig;
        $this->fastOrderService = $fastOrderService;
    }

    #[RouteScope(scopes: ['storefront'])]
    #[Route(path:"/fast-order", name:"frontend.fast_order", methods:["GET"])]    
    public function showForm(): Response
    {
        return new Response($this->twig->render('@FastOrder/storefront/page/fast-order.html.twig'));
    }

    public function isProductAvailable(string $productId): bool
    {
        $criteria = new Criteria([$productId]);
        $criteria->addFilter(new EqualsFilter('active', true));

        $product = $this->productRepository->search($criteria, Context::createDefaultContext())->first();
        return $product !== null;   
    }

    #[RouteScope(scopes: ['storefront'])]
    #[Route(path:"/fast-order/add", name:"frontend.fast_order_add", methods:["POST"])]
    public function addToCart(Request $request, Cart $cart, SalesChannelContext $context): RedirectResponse
    {
        $products = $request->get('products', []); // Array von Produktnummern und Mengen
        $sessionId = $request->getSession()->getId();
        $filteredProducts = [];

         // Extrahiere den Framework-Context aus dem SalesChannelContext
        $frameworkContext = $context->getContext();

        foreach ($products as $product) {
            $productNumber = $product['productNumber'];
            $quantity = (int) $product['quantity'];

            if ($quantity > 0 && $productNumber !="") {
                // UUID des Produkts anhand der Produktnummer abrufen
                $productId = $this->getProductIdByNumber($productNumber);

                // Erstelle ein LineItem
                $lineItem = (new LineItem(
                    Uuid::randomHex(),       // Einzigartige ID für dieses LineItem
                    LineItem::PRODUCT_LINE_ITEM_TYPE, // Typ des LineItems (Produkt)
                    $productId,              // Produktnummer
                    $quantity                // Menge
                ));

                // Füge das LineItem in den Warenkorb ein
                if ($this->isProductAvailable($productId)) {
                    
                    $this->cartService->add($cart, $lineItem, $context);
                    array_push($filteredProducts, $product);
                }
            }
        }

        // Produkte in die fast_order-Tabelle speichern
        $this->fastOrderService->saveFastOrder($filteredProducts, $sessionId, $frameworkContext);

    return $this->redirectToRoute('frontend.checkout.cart.page');
    }

    private function getProductIdByNumber(string $productNumber): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
    
        $product = $this->productRepository->search($criteria, Context::createDefaultContext())->first();
    
        return $product ? $product->getId() : null;
    }

    #[Route(path: "/api/fast-orders", name: "api.fast.orders", methods: ["GET"], defaults: ['XmlHttpRequest' => 'true', 'scopes'=> ["api"]])]
    public function getFastOrders(): JsonResponse
    {
        $criteria = new Criteria();

        $orders = $this->fastOrderService->getFastOrder($criteria, Context::createDefaultContext()); 

        return new JsonResponse($orders);
    }

  
    #[Route(path: "/fast-order/get-price", name:"frontend.fast_order.get_price", methods:["GET"])]
    public function getProductPrice(Request $request, Context $context): JsonResponse
    {
        $productNumber = $request->query->get('productNumber');

        if (!$productNumber) {
            return new JsonResponse(['error' => 'Product number is required'], 400);
        }

        $criteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
        $criteria->addFilter(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter('productNumber', $productNumber));
        
        $product = $this->productRepository->search($criteria, $context)->first();

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        return new JsonResponse([
            'productNumber' => $productNumber,
            'price' => $product->get('price')
        ]);
    }

}
