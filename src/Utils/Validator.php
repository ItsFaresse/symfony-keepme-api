<?php
/**
 * Created by PhpStorm.
 * User: adminHOC
 * Date: 13/02/2019
 * Time: 09:31
 */

namespace App\Utils;

use Symfony\Component\Console\Exception\InvalidArgumentException;

class Validator
{
    public function validateUsername(?string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('The username can not be empty.');
        }
        if (1 !== preg_match('^\w+[\w-\.]*\@\w+((-\w+)|(\w*))\.[a-z]{2,3}$', $username)) {
            throw new InvalidArgumentException('The username is a mail format');
        }
        return $username;
    }

    public function validatePassword(?string $password): string
    {
        if (empty($password)) {
            throw new InvalidArgumentException('The password can not be empty.');
        }
        if (1 !== preg_match('/[a-zA-Z]+/', $password) && mb_strlen(trim($password)) < 6) {
            throw new InvalidArgumentException('the password can matches any characters between a-z or A-Z. You can combine as much as you please and it must be at least 6 characters long.');
        }
        return $password;
    }

    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('The email can not be empty.');
        }
        if (false === mb_strpos($email, '@')) {
            throw new InvalidArgumentException('The email should look like a real email.');
        }
        return $email;
    }

}