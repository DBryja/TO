<?php
class UserController {
    private $userRepository;
    
    public function __construct($userRepository) {
        $this->userRepository = $userRepository;
    }
    
    public function registerUser($username, $password) {
        return $this->userRepository->register($username, $password);
    }
    
    public function loginUser($username, $password) {
        return $this->userRepository->login($username, $password);
    }
    
    public function getUser($id) {
        return $this->userRepository->getUserById($id);
    }
}