<?php

namespace Solvers\Dsql;

use Nette\Database\ResultSet;

class DashboardModel extends Application
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getDataCollectionForm($companyId = 0)
    {
        if(empty($companyId)){
            
        }
    }
}