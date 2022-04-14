<?php

use Phalcon\Mvc\Controller;

class RegisterController extends Controller
{
    /**
     * Default Action
     * Displays the Login Form
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->session->has('userDetail'))
        {
            $this->response->redirect();
        }
    }

    /**
     * Signup Action
     * Displays the Signup Form and signs up the user
     *
     * @return void
     */
    public function signupAction()
    {
        if ($this->request->isPost()) {

            //Initiating Object of User Class to Access the Database 
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

    /**
     * Login Function
     * public Function Logins the User
     *
     * @return void
     */
    public function loginAction()
    {
        if ($this->request->isPost()) {

            //Sanitizing and Creating a Key => Value Pair Array to insert into Database
            $inputData = array(
                'user_email' => $this->objects->sanitize->html($this->request->getPost('user_email')),
                'user_password' => $this->objects->sanitize->html($this->request->getPost('user_password'))
            );

            //Initiating Object of User Class to Access the Data and Assigning the Variables to the Class Variables
            $user = Users::find(
                [
                    'conditions' => 'user_email = :email: and user_password = :password:',
                    'bind'       => [
                        'email' => $inputData['user_email'],
                        'password' => $inputData['user_password'],
                    ]
                ]
            );

            //IF USER NOT FOUND Display Error
            if (!count($user)) {

                //Displaying Warning Message to User
                $this->view->success=false;
                $this->view->message = "Login failed! Kindly Check Email or Password";

                $this->logs->excludeAdapters(['main'])->warning("Login Failed for '" . $inputData['user_email'] . "' with Password '" . $inputData['user_password'] . "'");
            }
            //If User Found
            else {

                //Fetching User Detail and creating an Object to store into Session
                $user = $user[0];
                $userDetail = [
                    "user_id" => $user->user_id,
                    "user_name" => $user->user_name,
                    "user_email" => $user->user_email,
                    "user_role" => $user->user_role,
                ];
                $this->session->set('userDetail', (object)$userDetail);

                //Adding Information Log
                $this->logs->info("User Logged In: '" . $inputData['user_email'] . "'");

                //Redirect to Home
                $this->response->redirect();
            }
        }
    }

    /**
     * clear Session Function
     * Destroys Session and cookie
     *
     * @return void
     */
    public function clearAction()
    {
        $this->logs->info("User Logged Out: '" . $this->session->userDetail->user_email . "'");
        $this->session->destroy();
        // $this->cookie->get('cookieDetail')->delete();
        // $this->logs->excludeAdapters(['admin'])->info("'" . $this->session->get('userDetail')->user_email . "' Logged out");
        // $this->logs->info("'" . $this->session->get('userDetail')->user_email . "' Cookie Destroyed");
        $this->response->redirect("register");
    }
}
