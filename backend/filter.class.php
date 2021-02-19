<?php

require_once 'config.php';

class filter {
    const SORT_FIELD_EMAIL = 'Email';
    const SORT_FIELD_ADDEDAT = 'AddedAt';
    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';
    const SORT_FIELDS = [
        filter::SORT_FIELD_EMAIL,
        filter::SORT_FIELD_ADDEDAT,
    ];
    const SORT_ORDERS = [
        filter::SORT_ORDER_ASC,
        filter::SORT_ORDER_DESC
    ];
    
    /**
     * @var string
     */
    public $sortfield;
    
    /**
     * @var string
     */
    public $sortorder;
    
    /**
     * @var string|null
     */
    public $provider;
    
    /**
     * @var string|null
     */
    public $email;
    
    /**
     * @var integer|null
     */
    public $paginationblock;
    
    /**
     * @var string
     */
    private $whereKeyword = '';
    
    /**
     * @var string[] 
     */
    private $whereParts = [];
    
    /**
     * @var array 
     */
    public $executeParams = [];
    
    public function __construct(string $sortfield = null, string $sortorder = null, string $provider = null, string $email = null, int $paginationblock = null) {
        $this->sortfield = $sortfield;
        if (!in_array($this->sortfield, filter::SORT_FIELDS)) {
            $this->sortfield = filter::SORT_FIELD_ADDEDAT;
        }
        $this->sortorder = $sortorder;
        if (!in_array($this->sortorder, filter::SORT_ORDERS)) {
            $this->sortorder = filter::SORT_ORDER_ASC;
        }
        $this->provider = $provider;
        $this->email = $email;
        $this->paginationblock = $paginationblock;
        if (!$this->paginationblock || $this->paginationblock < 0) {
            $this->paginationblock = 0;
        }
    }
    
    /**
     * @global array $config
     * @return string
     */
    public function sql() {
        global $config;
        
        $this->whereKeyword = '';
        $this->whereParts = [];
        $this->executeParams = [];
        
        if ($this->provider) {
            $this->whereKeyword = ' where ';
            $this->whereParts[] = ' Provider = :provider ';
            $this->executeParams[':provider'] = $this->provider;
        }
        
        if ($this->email) {
            $this->whereKeyword = ' where ';
            $this->whereParts[] = ' Email like :email ';
            $this->executeParams[':email'] = '%' . $this->email . '%';
        }
        
        return 'select Id, Email, AddedAt from subscriptions ' . 
            $this->whereKeyword . implode(' and ', $this->whereParts) .
            ' order by ' . $this->sortfield . ' ' . $this->sortorder .
            ' limit ' . $this->paginationblock * $config['pagination'] . ', ' . ($config['pagination'] + 1); //+1 to check if there are more rows next
    }
}
