<?php

namespace FastOrder\Service;


use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;


class FastOrderService
{
    private EntityRepository $fastOrderRepository;
    private LoggerInterface $logger;
    private Connection $connection;

    public function __construct(DefinitionInstanceRegistry $definitionInstanceRegistry, LoggerInterface $logger, Connection $connection)
    {
        $this->fastOrderRepository = $definitionInstanceRegistry->getRepository('fast_order');
        $this->logger = $logger;
        $this->connection = $connection;
    }

    public function getFastOrder(Criteria $criteria, Context $context): array
    {
        $searchResult = $this->fastOrderRepository->search($criteria, $context);

        $array = [];

        foreach($searchResult as $item){
            array_push($array, [
                'id' => $item->getId(),
                'sessionId' => $item->getSessionId(),
                'productNumber' => $item->getProductNumber(),
                'quantity' => $item->getQuantity(),
                'customFields' => $item->getCustomFields(),
                'createdAt' => $item->getCreatedAt(),
                'updatedAt' => $item->getUpdatedAt()
            ]);
        }

        return $array;
    }
    

    public function saveFastOrder(array $orders, string $sessionId, Context $context): void
    {

        $data = [];

        foreach ($orders as $order) {
            $data[] = [
                'id' => Uuid::randomHex(),
                'sessionId' => $sessionId,
                'productNumber' => $order['productNumber'],
                'quantity' => (int)$order['quantity'],
                'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];
        }

        if (empty($data)) {
            $this->logger->warning('No orders provided for saving.');
            throw new \InvalidArgumentException('No orders provided for saving.');
        }

        try {
            $this->fastOrderRepository->create($data, $context);
            $this->logger->info('Fast orders saved successfully.', ['data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error('Error saving fast orders.', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }

    }
}
