<?php
class UserController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function registerUser($username, $password) {
        $this->userModel->register($username, $password);
    }

    public function loginUser($username, $password) {
        return $this->userModel->login($username, $password);
    }
}
