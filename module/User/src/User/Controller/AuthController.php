<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


/**
 * Class AuthController
 * @package User\Controller
 */
class AuthController extends AbstractActionController
{
    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $authService = $this->getServiceLocator()->get('AuthService');

        if ($authService->hasIdentity()) {
            $user = $this->getServiceLocator()->get('User\Model\UserTable')->getUser($authService->getIdentity());
            return  new ViewModel(array('firstname' => $user->name));
        } else {
            return $this->redirect()->toRoute('login');
        }
    }
}