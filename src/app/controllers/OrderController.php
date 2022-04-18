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
        $filterStatus = 'All';
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d');

        if ($this->request->isPost('filterDate')) {
            if ($this->request->getPost('filterDate') != 'Custom') {
                $startDate = date('Y-m-d', strtotime($this->request->getPost('filterDate')));
            }
        }

        if ($this->request->isPost('startDate')) {
            $startDate = $this->request->getPost('startDate');
        }

        if ($this->request->isPost('endDate')) {
            $endDate = $this->request->getPost('endDate');
        }

        $search = [
            [
                '$match' => [
                    'order_date' => [
                        '$gte' => $startDate,
                        '$lte' => $endDate
                    ]
                ],
            ]
        ];

        if ($this->request->isPost('filterStatus')) {
            $filterStatus = $this->request->getPost('filterStatus');
        }

        if ($filterStatus != 'All') {
            array_push($search, [
                            '$match' => [
                                'order_status' => $filterStatus
                            ]
                        ]);
        }

        if (1) {
            // $search =
            //     [
            //         [
            //             '$match' => [
            //                 'order_date' => [
            //                     '$gte' => $startDate,
            //                     '$lte' => $endDate
            //                 ]
            //             ],
            //         ],
            //         [
            //             '$match' => [
            //                 'order_status' => $filterStatus
            //             ]
            //         ]
            //     ];
            // echo "<pre>";
            // print_r($search);
            // die;
            $result = $this->mongo->order->aggregate($search);
        } else {
            $result = $this->mongo->order->find();
        }

        $html = '';
        foreach ($result as $key => $value) {
            $html .= '
                <tr>
                    <td>
                        ' . $value->customer_name . '
                    </td>
                    <td>
                        ' . $value->product_quantity . '
                    </td>
                    <td>
                        ' . ++$value->variant . '
                    </td>
                    <td>
                        ' . $value->order_date . '
                    </td>
                    <td id="status' . $value->_id . '">
                        ' . $value->order_status . '
                    </td>
                    <td>
                        <select name="status" id="statusUpdateOrder" class="form-control" data-id="' . $value->_id . '">
                            <option>Paid</option>
                            <option>Processing</option>
                            <option>Dispatched</option>
                            <option>Shipped</option>
                            <option>Refunded</option>
                        </select>
                    </td>
                    <td>
                        <a href="/order/delete?id=' . $value->_id . '" class="btn btn-danger">Delete</a>
                    </td>
                    <td>
                        <a data-id="' . $value->product_id . '" class="quickPeek btn btn-primary text-light" data-toggle="modal" data-target="#exampleModal">
                            Quick Peek
                        </a>
                    </td>
                    ';
            $html .= '</tr>';
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
        $this->view->product = (new Orders())->listProductsOrderPage($this->mongo);

        if ($this->request->isPost()) {
            $order = array(
                'customer_name' => $this->request->getPost('customer_name'),
                'product_id' => $this->request->getPost('product_id'),
                'product_quantity' => $this->request->getPost('product_quantity'),
                'order_status' => 'Processing',
                'order_date' => date('Y-m-d')
            );
            if (null !== $this->request->getPost('variant')) {
                $order['variant'] = $this->request->getPost('variant');
            }

            $this->mongo->order->insertOne($order);
            $this->response->redirect('order');
        }
    }

    /**
     * Delete Action
     * Deletes the product by _id passed in get 'id'
     *
     * @return void
     */
    public function deleteAction()
    {
        if (null !== $this->request->getQuery('id')) {
            $this->mongo->order->deleteOne(["_id" => new MongoDB\BSON\ObjectId($this->request->getQuery('id'))]);
            $this->response->redirect("/order");
        }
    }

    private function filterDate($startDate, $endDate)
    {
        $result = $this->mongo->order->find([
            'order_date' => [
                '$gte' => $startDate, '$lte' => $endDate
            ]
        ]
        );
        return $result;
    }

    public function updateStatusAction()
    {
        $result = $this->mongo->order->updateOne(
            [
                '_id' => new MongoDB\BSON\ObjectId($this->request->getPost('id'))
            ],
            [
                '$set' => [
                    'order_status' => $this->request->getPost('status')
                ]
            ]
        );
        return json_encode($result);
    }
}
