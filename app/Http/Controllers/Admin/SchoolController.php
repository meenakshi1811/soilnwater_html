<?php

namespace App\Http\Controllers\Admin;

class SchoolController extends InstituteController
{
    protected string $ownerRole = 'school';

    protected string $adminRoutePrefix = 'admin.schools';

    protected string $portalRoutePrefix = 'school';

    protected string $publicRouteName = 'schools.show';

    protected string $entityLabel = 'School';

    protected string $accountLabel = 'School account';

    protected string $createAccountRole = 'school';
}
