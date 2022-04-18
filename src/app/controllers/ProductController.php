<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * Index Action
     * Lists all the Product
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->content = $this->listing();
    }

    /**
     * Add Action
     * Displays the Form and inserts to Database
     *
     * @return void
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            if (null !== $this->request->isPost('additionalKey')) {
                $meta = array_combine($this->request->getPost('additionalKey'), $this->request->getPost('additionalvalue'));
            }

            if (null !== $this->request->isPost('variationKey')) {
                $varient = array();
                for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                    array_push($varient, array_combine($this->request->getPost('variationKey')[$i], $this->request->getPost('variationValue')[$i]));
                }
                for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                    $varient[$i]["VarientPrice"] = $this->request->getPost("variationPrice".$i);
                }
            }

            $product = array(
                'product_name' => $this->request->getPost('product_name'),
                'product_category' => $this->request->getPost('product_category'),
                'product_stock' => $this->request->getPost('product_stock'),
                'product_price' => $this->request->getPost('product_price'),
            );
            if (isset($meta)) {
                $product['meta'] = $meta;
            }
            if (isset($varient)) {
                $product['varient'] = $varient;
            }
            $this->mongo->product->insertOne($product);
            $this->response->redirect('product');
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
            $this->mongo->product->deleteOne(["_id" => new MongoDB\BSON\ObjectId($this->request->getQuery('id'))]);
            $this->response->redirect("/product");
        }
    }

    /**
     * productDetail
     * returns the Product detail
     *
     * @return void
     */
    public function productDetailAction()
    {
        $result = $this->mongo->product->findOne(["_id" => new MongoDB\BSON\ObjectId($this->request->getPost('id'))]);
        return json_encode($result);
    }

    /**
     * searchProducts
     * returns the Products
     *
     * @return void
     */
    public function searchProductsAction()
    {
        $result = $this->mongo->product->find(["product_name" => $this->request->getPost('name')]);
        $Values=array();
        foreach ($result as $value) {
            array_push($Values, $value);
        }
        return json_encode($value);
    }

    private function listing()
    {
        $result = $this->mongo->product->find();
        $html = '';
        foreach ($result as $key => $value) {
            $html .= '
                <tr>
                    <td>
                        ' . $value->product_name . '
                    </td>
                    <td>
                        ' . $value->product_category . '
                    </td>
                    <td>
                        ' . $value->product_price . '
                    </td>
                    <td>
                        ' . $value->product_stock . '
                    </td>
                    <td>
                        <a href="/product/delete?id=' . $value->_id . '" class="btn btn-danger">Delete</a>
                    </td>
                    <td>
                        <a data-id="' . $value->_id . '" class="quickPeek btn btn-primary text-light" data-toggle="modal" data-target="#exampleModal">
                            Quick Peek
                        </a>
                    </td>
                    ';
            // if (isset($value->label)) {
            //     $html.='<td>';
            //     for ($i=0; $i<count($value->label); $i++) {
            //         $html.=$value->label[$i].' : '.$value->value[$i].'<br>';
            //     }
            //     $html.='</td>';
            // }
            $html .= '</tr>';
        }
        // $html .= '</table>';
        return $html;
    }
}
