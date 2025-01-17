//<plugin-root>/src/Resources/app/administration/src/module/<your module's name>/page/<your component name>

import template from './fast-order-index.html.twig';
import './fast-order-index.scss';

const { Component } = Shopware;

Component.register('fast-order-index', {
    template,
    inject: ['systemConfigApiService'],
    data() {
        return {
            loading: false,
            orders: [],
            env: null
        };
    },

    methods: {
        async loadOrders() {
            this.loading = true;
            this.token = null;
            try {
                let tokenResponse = await fetch('/api/oauth/token',{
                    method: "POST",
                    headers: new Headers({                       
                        'Content-Type': 'application/json'
                    }),
                    body: JSON.stringify({ 
                        grant_type: "client_credentials",
                        client_id: this.env.adminaccesskey,
                        client_secret: this.env.adminaccesssecret
                    })
                })
                let tokenData = await tokenResponse.json();
                this.token = tokenData.access_token;
                let response = await fetch('/api/fast-order',{
                    headers: new Headers({
                        'Authorization': 'Bearer ' + this.token,
                        'Content-Type': "application/json"
                    })
                });
                let data = await response.json();
                this.orders = data.data.map((order)=>{
                    order["comment"] = order.attributes?.customFields?.comment ? order.attributes?.customFields?.comment : ''
                    return order;
                })
            } catch (error) {
                console.error('Failed to load orders:', error);
            } finally {
                this.loading = false;
            }
        },
        async saveComment(index) {
            const order = this.orders[index];
          
            let response = await fetch(`/api/fast-order/${order.id}`,{
                method: "PATCH",
                headers: new Headers({
                    'Authorization': 'Bearer ' + this.token,
                    'Content-Type': "application/json"
                }),
                body: JSON.stringify({ 
                    customFields: {comment: order.comment}
                })
            })
            alert("Comment saved!", response);
        
        },
        async getConfig(){
            const data = await this.systemConfigApiService.getValues('fastorder.config');
    
            if(Object.keys(data).length && Object.hasOwn(data, 'FastOrder.config.adminaccesskey') && Object.hasOwn(data, 'FastOrder.config.adminaccesssecret')){
                this.env = {
                    adminaccesskey: data["FastOrder.config.adminaccesskey"],
                    adminaccesssecret: data["FastOrder.config.adminaccesssecret"]
                }
            }
        }
    },
    mounted() {
        this.getConfig()
    } 
    
});

  