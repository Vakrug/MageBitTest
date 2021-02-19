<?php

require_once 'db.class.php';
require_once 'functions.php';
require_once 'filter.class.php';
require_once 'config.php';

class subscription {
    const ERROR_EMAIL_REQUIRED = 'Email address is required';
    const ERROR_EMAIL_NOT_VALID = 'Please provide a valid e-mail address';
    const ERROR_EMAIL_FROM_COLOMBIA = 'We are not accepting subscriptions from Colombia emails';
    const ERROR_TOS_NOT_AGREED = 'You must accept the terms and conditions';
    const ERROR_DB = 'Failed to insert record to database';
    
    /**
     * @var string subscription email address.
     */
    public $email;
    
    /**
     * @var bool terms of service agreed
     */
    public $tos_agreed;
    
    /**
     * @var string[] validation errors
     */
    public $errorMessages = [];
    
    /**
     * @var \PDO database connection
     */
    private $dbh = null;
    
    /**
     * @var bool for pagination
     */
    public $hasMoreSubscriptions = false;
    
    /**
     * @param string $email
     * @param bool $tos_agreed
     */
    public function __construct(string $email = null, bool $tos_agreed = false) {
        $this->email = $email;
        $this->tos_agreed = $tos_agreed;
    }
    
    /**
     * @return \PDO database connection
     */
    private function getConnection() {
        if (!$this->dbh) {
            $this->dbh = db::connect();
        }
        return $this->dbh;
    }
    
    /**
     * @return boolean
     */
    public function validate() {
        $this->errorMessages = [];
        $error = false;
        
        if (!$this->email) {
            $error = true;
            $this->errorMessages[] = subscription::ERROR_EMAIL_REQUIRED;
        } else {
            $email = filter_var($this->email, FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $error = true;
                $this->errorMessages[] = subscription::ERROR_EMAIL_NOT_VALID;
            } else {
                if (endsWith(strtolower($this->email), '.co')) {
                    $error = true;
                    $this->errorMessages[] = subscription::ERROR_EMAIL_FROM_COLOMBIA;
                }
            }
        }

        if (!$this->tos_agreed) {
            $error = true;
            $this->errorMessages[] = subscription::ERROR_TOS_NOT_AGREED;
        }
        
        return !$error;
    }
    
    /**
     * @return string
     */
    public function provider() {
        return get_string_between($this->email, '@', '.');
    }
    
    /**
     * @return boolean
     */
    public function save() {
        $this->errorMessages = [];
        
        $dbh = $this->getConnection();
        $sql = 'insert into subscriptions (Email, Provider, AddedAt) values (:email, :provider, NOW())';
        $sth = $dbh->prepare($sql);
        $success = $sth->execute([
            ':email' => $this->email,
            ':provider' => $this->provider()
        ]);

        if (!$success) {
            $this->errorMessages[] = subscription::ERROR_DB;
        }
        
        return $success;
    }
    
    /**
     * @param integer[] $Ids
     * @return array
     */
    public function forExport(array $Ids) {
        $dbh = $this->getConnection();
        $sql = 'select Email, AddedAt from subscriptions where Id in (' . implode(',', $Ids) . ')';
        $sth = $dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * @param int $Id
     */
    public function delete(int $Id) {
        $dbh = $this->getConnection();
        $sql = 'delete from subscriptions where Id = :Id';
        $sth = $dbh->prepare($sql);
        $sth->execute([
            ':Id' => $Id
        ]);
    }
    
    /**
     * @return array
     */
    public function providers() {
        $dbh = $this->getConnection();
        $sql = 'select Provider from subscriptions group by Provider order by Provider asc';
        $sth = $dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * @global array $config
     * @param filter $filter
     * @return array
     */
    public function subscriptions(filter $filter) {
        global $config;
        $dbh = $this->getConnection();
        $sth = $dbh->prepare($filter->sql());
        $sth->execute($filter->executeParams);
        $rows = $sth->fetchAll();
        $this->hasMoreRows = false;
        if (count($rows) == $config['pagination'] + 1) {
            $this->hasMoreRows = true;
            array_pop($rows); //Remove that last element. It served its purpose.
        }
        return $rows;
    }
}
