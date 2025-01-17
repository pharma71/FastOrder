import './page/fast-order-index';

const { Module } = Shopware;

Module.register('fast-order-module', {
    type: 'plugin',
    name: 'FastOrderModule',
    title: 'Fast Order',
    description: 'Manage fast orders in the admin area',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag',

    routes: {
        index: {
            component: 'fast-order-index',
            path: 'index',
        },
    },

    navigation: [
        {
            label: 'Fast Orders',
            color: '#ff3d58',
            path: 'fast.order.module.index',
            icon: 'default-shopping-paper-bag',
            position: 100,
            parent: 'sw-content'
        },
    ],
});
