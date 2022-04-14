<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * Index Function
     * lists the Products Available
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->content = $this->displayProducts();
    }

    /**
     * Fetches the Products form the product table, adds to html and returns to the index Action for listing
     *
     * @return html
     */
    private function displayProducts()
    {
        $products = Products::find();
        $html = '';
        foreach ($products as $value) {
            $html .= '
            <tr class="text-align-center">
            <td class="p-3 text-start">' . $value->product_id . '</td>
            <td class="p-3">' . $value->product_name . '</td>
            <td class="p-3 fst-italic">' . $value->product_description . '</td>
            <td class="p-3 fst-italic">' . $value->product_tags . '</td>
            <td class="p-3">' . $value->product_price . '</td>
            <td class="p-3">' . $value->product_stock . '</td>
            <td><a href="delete?'.BEARER.'='.$this->request->getQuery(BEARER).'&id=' . $value->product_id . '" class="p-2 btn btn-danger">Delete</a></td>
            ';
        }
        return $html;
    }

    public function deleteAction()
    {
        if ($this->request->isGet()) {
            
            $product = Products::find(
                [
                    'conditions' => 'product_id=:id:',
                    'bind' => [
                    'id' => $this->request->getQuery('id')
                    ]
                ]
            );
            $product->delete();
            $this->response->redirect('product/index?'.BEARER.'='.$this->request->getQuery(BEARER));
        }
    }

    /**
     * Add Function to add Products
     *
     * @return void
     */
    public function addAction()
    {
        if ($this->request->isPost()) {

            $product = new Products();

            //Event Fire and handled in listener class
            $event = $this->EventManager;
            $inputdata = $event->fire('listener:addProduct', $this);

            $product->assign(
                $inputdata,
                [
                    'product_name',
                    'product_description',
                    'product_tags',
                    'product_price',
                    'product_stock'
                ]
            );

            $success = $product->save();

            if ($success) {
                Products::find(
                    [
                        'conditions' => 'product_name = :name: and product_description = :description: and product_tags = :tags: and product_price = :price: and product_stock = :stock:',
                        'bind'       => [
                            'name' => $inputdata['product_name'],
                            'description' => $inputdata['product_description'],
                            'tags' => $inputdata['product_tags'],
                            'price' => $inputdata['product_price'],
                            'stock' => $inputdata['product_stock']
                        ]
                    ]
                )[0];
                $this->logs->info("Product Added: '" . $product->product_id . "'");
            } else {
                $this->logs->critical("Product Not Added succesfully due to following reason: <br>" . implode("<br>", $product->getMessages()));
            }
            $this->response->redirect("product/?" . BEARER . "=" . $this->request->getQuery(BEARER));
        }
    }
}
