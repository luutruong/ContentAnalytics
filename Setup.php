<?php

namespace Truonglv\ContentAnalytics;

use XF\Db\Schema\Create;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use Truonglv\ContentAnalytics\DevHelper\SetupTrait;

class Setup extends AbstractSetup
{
    use SetupTrait;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $this->doCreateTables($this->getTables());
    }

    public function uninstallStep1()
    {
        $this->doDropTables($this->getTables());
    }

    protected function getTables1()
    {
        $tables = [];

        $tables['xf_tl_content_analytics_data'] = function (Create $table) {
            $table->addColumn('data_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('content_type', 'varchar', 25);
            $table->addColumn('content_id', 'int')->unsigned();
            $table->addColumn('content_date', 'int')->unsigned();
            $table->addColumn('count', 'int')->unsigned()->setDefault(0);

            $table->addKey(['content_type', 'content_id', 'content_date'], 'content_type_id_date');
            $table->engine('MyISAM');
        };

        return $tables;
    }
}
