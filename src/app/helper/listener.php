<?php

namespace helper;

use ProductController;
use OrderController;
use Phalcon\Acl\Adapter\Memory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Events\Event;
use Phalcon\Di\Injectable;

class listener extends Injectable
{
    /**
     * Before Handle Request
     * Function to Handle ACL File and Role
     * @param [type] $event
     * @param [type] $application
     * @return void
     */
    public function beforeHandleRequest($event, $application)
    {
        //Getting Role 
        $role = $this->getRole();

        /*
        * Getting Token for Role using JWT 3rd Party Token
        * if token set is not same as user change token else skip
        */
        if (!$this->resolveToken() == $role) {
            $bearer = $this->createToken($role);
        } else {
            $bearer = $this->request->getQuery(BEARER);
        }
        // $this -> buildACL();
        $this->useACL($application);
    }

    private function useACL($application)
    {
        $aclFile = APP_PATH . '/secure/acl.cache';

        //Check whether ACL Data Exists Already
        if (is_file($aclFile)) {
            $acl = unserialize(
                file_get_contents($aclFile)
            );

            $role = $application->request->get(BEARER);

            //IF NO CONTROLLER GIVEN
            if ($this->router->getControllerName() == '') {
                $controller = 'index';
            } else {
                $controller = $this->router->getControllerName();
            }

            //IF NO ACTION GIVEN
            if ($this->router->getActionName() == '') {
                $action = 'index';
            } else {
                $action = $this->router->getActionName();
            }

            //GRANT ACCESS
            if (!$role || true !== $acl->isAllowed($this->resolveToken(), $controller, $action)) {
                echo "Access Denied! 68 listener";
                die;
            }
        } else {
            echo "No ACL File. Kindly Try after sometime";
            die;
        }
    }

    private function buildACL()
    {
        $aclFile = APP_PATH . '/secure/acl.cache';
        //Check whether ACL Data Exists Already
        if (true !== is_file($aclFile)) {

            //Build ACL if not Exist
            $acl = new Memory();

            /**
             * Setup for ACL
             */
            // $acl->addRole('programmer');
            $acl->addRole('accountant');
            $acl->addRole('admin');
            $acl->addRole('manager');

            $acl->addComponent(
                'index',
                [
                    'index'
                ]
            );

            $acl->addComponent(
                'order',
                [
                    'add',
                    'index',
                    ''
                ]
            );

            $acl->addComponent(
                'product',
                [
                    'add',
                    'index',
                    ''
                ]
            );

            // $acl->allow('programmer', 'access', '*');
            $acl->allow('admin', '*', '*');
            $acl->allow('accountant', 'order', '*');
            $acl->allow('accountant', 'index', '*');
            $acl->allow('manager', 'product', '*');
            $acl->allow('manager', 'index', '*');




            //Put content in ACL File in serialized format
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        } else {
            //Restore ACL object from serialized file
            $acl = unserialize(
                file_get_contents($aclFile)
            );
        }
    }

    /**
     * get Role Function
     * gets the role from session for signed in User or returns the default Role = 'guest'
     *
     * @return void
     */
    private function getRole()
    {
        if ($this->session->has('userDetail')) {
            return $this->session->userDetail->user_role;
        } else {
            return 'guest';
        }
    }

    /**
     * Creates 3rd Party Token using JWT
     *
     * @param [type] $role
     * @return void
     */
    public function createToken($role)
    {
        $key = "anugrah_vishwas_paul";
        $now        = new \DateTime();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();

        $payload = array(
            "iat" => $issued,
            "nbf" => $notBefore,
            "sub" => $role
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    /**
     * resolves JWT Token
     *
     * @return void
     */
    private function resolveToken()
    {
        $key = "anugrah_vishwas_paul";
        $tokenReceived = $this->request->getQuery(BEARER);
        $now           = new \DateTime();
        $expires       = $now->modify('+1 day')->getTimestamp();
        $decoded = JWT::decode($tokenReceived, new Key($key, 'HS256'));
        return $decoded->sub;
    }


    /**
     * addProduct Event Handler
     * Returns the input Data
     *
     * @param Event $event
     * @param ProductController $obj
     * @return array
     */
    public function addProduct(Event $event, ProductController $obj)
    {
        $setting = \Settings::find()[0];
        $inputdata = '';

        if ($setting->title_optimization === 'With tags') {
            $inputdata = array(
                'product_name' => $this->objects->sanitize->html($this->request->getPost('product_name')) . $this->objects->sanitize->html($this->request->getPost('product_tags')),
                'product_description' => $this->objects->sanitize->html($this->request->getPost('product_description')),
                'product_tags' => $this->objects->sanitize->html($this->request->getPost('product_tags')),
            );
        } else {
            $inputdata = array(
                'product_name' => $this->objects->sanitize->html($this->request->getPost('product_name')),
                'product_description' => $this->objects->sanitize->html($this->request->getPost('product_description')),
                'product_tags' => $this->objects->sanitize->html($this->request->getPost('product_tags')),
            );
        }

        /**
         * If No Price Given Using Default Price
         */
        if ($this->request->getPost('product_price') === '') {
            $inputdata['product_price'] = $setting->default_price;
        }
        /**
         * Using Given Price
         */
        else {
            $inputdata['product_price'] = $this->objects->sanitize->html($this->request->getPost('product_price'));
        }

        /**
         * If No Stock Given Using Default Stock
         */
        if ($this->request->getPost('product_stock') === '') {
            $inputdata['product_stock'] = $setting->default_stock;
        }
        /**
         * Using Given Stock
         */
        else {
            $inputdata['product_stock'] = $this->objects->sanitize->html($this->request->getPost('product_stock'));
        }
        return $inputdata;
    }

    /**
     * add Order
     *
     * @param Event $event
     * @param OrderController $obj
     * @return array
     */
    public function addOrder(Event $event, OrderController $obj)
    {
        $setting = \Settings::find()[0];
        
        $inputdata = array(
            'customer_name' => $this->objects->sanitize->html($this->request->getPost('customer_name')),
            'customer_address' => $this->objects->sanitize->html($this->request->getPost('customer_address')),
            'product' => $this->objects->sanitize->html($this->request->getPost('product')),
            'product_quantity' => $this->objects->sanitize->html($this->request->getPost('product_quantity')),
        );

        /**
         * If No Zipcode Given Using Default Zipcode
         */
        if ($this->request->getPost('zipcode') === '') {
            $inputdata['zipcode'] = $setting->default_zipcode;
        }/**
         * Using Given Zipcode
         */ else {
            $inputdata['zipcode'] = $this->objects->sanitize->html($this->request->getPost('zipcode'));
        }

        return $inputdata;
    }
}
