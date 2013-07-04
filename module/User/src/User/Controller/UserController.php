<?php
namespace User\Controller;

use User\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Model\User;
use User\Model\RegUser;
use User\Form\UserForm;


/**
 * Class UserController
 * @package User\Controller
 */
class UserController extends AbstractActionController
{
    protected $userTable;

    protected $form;

    protected $storage;

    protected $authservice;


    /**
     * @return array|object
     */
    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                ->get('AuthService');
        }

        return $this->authservice;
    }


    /**
     * @return array|object
     */
    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()
                ->get('User\Model\AuthStorage');
        }

        return $this->storage;
    }


    /**
     * @return array|object
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }


    /**
     * @return array|\Zend\Http\Response
     */
    public function loginAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('success');
        }

        $form = new UserForm();
        $form->get('submit')->setValue('Log in');
        $request = $this->getRequest();
        $f = $request->getPost();

        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getAuthService()->getAdapter()
                    ->setIdentity($request->getPost('login'))
                    ->setCredential($request->getPost('password'));
                $result = $this->getAuthService()->authenticate();

                if ($result->isValid()) {
                    if ($request->getPost('rememberme') == 1) {
                        $this->getSessionStorage()->setRememberMe(1);
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->getStorage()->write($request->getPost('login'));
                }
                $this->redirect()->toRoute('success');
            }
        }

        return array('form' => $form);
    }


    /**
     * @return array|\Zend\Http\Response
     */
    public function registerAction()
    {
        $form = new UserForm();
        $form->get('submit')->setValue('Register');
        $request = $this->getRequest();

        if ($request->isPost()) {
            $user = new RegUser();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUserTable()->registerUser($request->getPost());

                return $this->redirect()->toRoute('login');
            }
        }
        return array('form' => $form);
    }


    /**
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('login');
    }
}