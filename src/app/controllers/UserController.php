<?php

use Phalcon\Mvc\Controller;

class UserController extends Controller
{
    /**
     * Index Function
     * lists the Users Available
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->content = $this->displayUsers();
    }

    /**
     * Fetches the User form the userd table, adds to html and returns to the index Action for listing
     *
     * @return html
     */
    private function displayUsers()
    {
        $users = Users::find();
        $html = '';
        foreach ($users as $user) {
            $html .= '
            <tr class="text-align-center">
            <td class="p-3 text-start">' . $user->user_id . '</td>
            <td class="p-3">' . $user->user_name . '</td>
            <td class="p-3 fst-italic">' . $user->user_email . '</td>
            <td class="p-3 fst-italic">' . $user->user_password . '</td>
            <td class="p-3">' . $user->user_role . '</td>
            <td><a href="delete?'.BEARER.'='.$this->request->getQuery(BEARER).'&id=' . $user->user_id . '" class="p-2 btn btn-danger">Delete</a></td>
            ';
        }
        return $html;
    }

    public function deleteAction()
    {
        if ($this->request->isGet()) {
            
            $user = Users::find(
                [
                    'conditions' => 'user_id=:id:',
                    'bind' => [
                    'id' => $this->request->getQuery('id')
                    ]
                ]
            );
            $success = $user->delete();
            if ($success) {
                $this->logs->info("User Deleted: '" . $this->request->getQuery('id') . "'");
            } else {
                $this->logs->critical("User Not Deleted succesfully due to following reason: <br>" . implode("<br>", $user->getMessages()));
            }
            $this->response->redirect('user/index?'.BEARER.'='.$this->request->getQuery(BEARER));
        }
    }

    /**
     * Add Function to add Users
     *
     * @return void
     */
    public function addAction()
    {
        if ($this->request->isPost()) {

            $user = new Users();

            //Sanitizing and Creating a Key => Value Pair Array to insert into Database
            $inputData = array(
                'user_name' => $this->objects->sanitize->html($this->request->getPost('user_name')),
                'user_email' => $this->objects->sanitize->html($this->request->getPost('user_email')),
                'user_password' => $this->objects->sanitize->html($this->request->getPost('user_password'))
            );

            //Assigning the Variables to the Class Variables
            $user->assign(
                $inputData,
                [
                    'user_name',
                    'user_email',
                    'user_password'
                ]
            );

            //Returning True or False from the Database
            $success = $user->save();

            //Checking if Adding User is Successful
            if ($success) {
                
                //Displaying Success Message to User
                $this->view->success=true;
                $this->view->message = "User Registered succesfully";
                
                //Adding Information Log
                $this->logs->info("User Registered: '" . $inputData['user_email'] . "'");
            } else {

                //Displaying Warning Message to User
                $this->view->success=false;
                $this->view->message = "Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages());

                //Adding Critical Log
                $this->logs->critical("User: '" . $inputData['user_email'] . "' Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages()));
            }

        }
    }
}
