<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    /**
     * Index Action
     * Lists all the Orders
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->content = $this->displayOrders();
    }

    /**
     * Display Orders
     * Fetches the Data from Database and converts to HTML and returns to Index Action
     *
     * @return void
     */
    private function displayOrders()
    {
        $orders = Orders::find();
        $html='';

        foreach ($orders as $value)
        {
            $prod_name = Products::find(
                [
                    'conditions' => 'product_id = :id:',
                    'bind'       => [
                        'id' => $value->product
                    ]
                ]
            )[0];
            $html.= '
            <tr class="text-align-center">
                <td class="p-3 text-start">' . $value->order_id . '</td>
                <td class="p-3">' . $value->customer_name . '</td>
                <td class="p-3 fst-italic">' . $value->customer_address . '</td>
                <td class="p-3 fst-italic">' . $value->zipcode . '</td>
                <td class="p-3">' . $prod_name->product_name. '</td>
                <td class="p-3">' . $value->product_quantity . '</td>
                ';
        }
        return $html;
    }

    /**
     * Add Action
     * Adds Order
     *
     * @return void
     */
    public function addAction()
    {
        $this->view->product = Products::find();
        // $settings = Settings::find();

        if ($this->request->isPost()) {

            $order = new Orders();

            //Event Fire
            $event = $this->EventManager;
            $inputdata = $event -> fire('listener:addOrder', $this);

            $order->assign(
                $inputdata,
                [
                    'customer_name',
                    'customer_address',
                    'zipcode',
                    'product',
                    'product_quantity'
                ]
            );

            $success = $order->save();

            if ($success) {
                Orders::find(
                    [
                        'conditions' => 'customer_name = :name: and customer_address = :address: and zipcode = :zip: and product = :prod: and product_quantity = :qty:',
                        'bind'       => [
                            'name' => $inputdata['customer_name'],
                            'address' => $inputdata['customer_address'],
                            'zip' => $inputdata['zipcode'],
                            'prod' => $inputdata['product'],
                            'qty' => $inputdata['product_quantity']
                        ]
                    ]
                )[0];
                $this->logs->info("Order Added: '" . $order->order_id . "'");
            } 
            else {
                $this->logs->critical("Order Not Added succesfully due to following reason: <br>" . implode("<br>", $order->getMessages()));
            }
            $this->response->redirect("order?".BEARER."=".$this->request->getQuery(BEARER));
        }
    }
}