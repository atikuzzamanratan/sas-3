<?php

namespace Solvers\Dsql;

use Nette\Database\ResultSet as ResultSetAlias;
use Solvers\Dsql\Application;

class Dashboard extends Application
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getNotifications($userId, $companyId): ResultSetAlias
    {
        $session = $_SESSION;

        $userId = is_null($userId) ? $session['UserId'] : $userId;
        $companyId = is_null($companyId) ? $session['CompanyId'] : $companyId;

        return $this->getDBConnection()->query('SELECT [id],[FromUserID],[ToUserID],[Notification],[Status],[DataEntryDate],[NotificationReadTime]
                                    FROM [dbo].[Notification] WHERE [ToUserID]=? AND Status = 0 AND loggedUserCompanyID=?
                                    ORDER BY [DataEntryDate] DESC', $userId, $companyId);
    }
}