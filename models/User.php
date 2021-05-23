<?php
/**
    Class User
    @package app\models
*/

namespace app\models;
use app\core\UserModel;

//RegisterModel name changed to User
class User extends UserModel {
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public int $id = 0;
    public string $firstname = '';
    public string $lastname = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';
    public string $profile_picture = '';
    public int $blocked = 0;
    public string $state = '';
    public string $city = '';
    public string $street = '';
    public string $zip= '';
    public string $type = 'client';

    public function tableName(): string
    {
        return 'user';
    }

    public function save() {
        $this->status = self::STATUS_INACTIVE;
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();        
    }

    public function rules(): array {
        return  [
            'firstname' => [self::RULE_REQUIRED],
            'lastname' => [self::RULE_REQUIRED],
            'username' => [self::RULE_REQUIRED, [self::RULE_UNIQUE, 'class' => self::class]],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, [
                self::RULE_UNIQUE, 'class' => self::class
            ]],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8], [self::RULE_MAX, 'max' => 24]],
            'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
            'state' => [self::RULE_REQUIRED],
            'street' => [self::RULE_REQUIRED],
            'city' => [self::RULE_REQUIRED],
            'zip' => [self::RULE_REQUIRED]
        ];
    }

    public function hasError($attribute) {
        return $this->errors[$attribute] ?? false;
    }

    public function primaryKey(): string {
        return 'id';
    }

    public function attributes():array
    {
        return ['username','firstname', 'lastname', 'email', 'password','profile_picture', 'blocked', 'state', 'city', 'street', 'zip','type'];
    }

    public function displayName(): string {
        return $this->firstname.' '.$this->lastname;
    }

    public function labels(): array
    {
        return [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
            'blocked' => 'Blocked',
            'username' => 'Username',
            'state' => 'State',
            'city' => 'City',
            'street' => 'Street',
            'zip' => 'Zip'
        ];
    }
}