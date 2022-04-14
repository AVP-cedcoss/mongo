<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->language = array(
            'en' => 'English',
            'du' => 'Dutch',
            'fr' => 'French',
            'hb' => 'Hebrew',
        );

        $data = array(
            'Product' => $this->objects->translate->_('Product'),
            'Order' => $this->objects->translate->_('Order'),
            'Settings' => $this->objects->translate->_('Settings'),
            'Access' => $this->objects->translate->_('Access'),
            'Signup' => $this->objects->translate->_('Signup'),
            'Have a Wonderful Day' => $this->objects->translate->_('Have a Wonderful Day')
        );

        $this->view->list = $data;

        if ($this->request->getPost('action')) {
            $this->response->redirect("index/index?lang=" . $this->request->getPost('lang') . "&".BEARER."=" . $this->request->getQuery(BEARER));
        }
    }
}