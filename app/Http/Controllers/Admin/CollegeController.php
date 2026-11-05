<?php

namespace App\Http\Controllers\Admin;

class CollegeController extends InstituteController
{
    protected string $ownerRole = 'college';

    protected string $adminRoutePrefix = 'admin.colleges';

    protected string $portalRoutePrefix = 'college';

    protected string $publicRouteName = 'colleges.show';

    protected string $entityLabel = 'College';

    protected string $accountLabel = 'College account';

    protected string $createAccountRole = 'college';
}
