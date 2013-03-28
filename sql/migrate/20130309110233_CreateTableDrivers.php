<?php

/**
 * Phigrate
 *
 * PHP Version 5
 *
 * @category  Phigrate
 * @package   Migrations
 * @author    Manuel HERVO <manuel.hervo % gmail . com>
 * @author    Cody Caughlan <codycaughlan % gmail . com>
 * @copyright 2007 Cody Caughlan (codycaughlan % gmail . com)
 * @license   GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/Azema/Phigrate
 */

/**
 * Class migration DB CreateTableLicensees
 *
 * For documentation on the methods of migration
 *
 * @see http://blog.phigrate.org/doc/methodsMigrations
 *
 * @category  Rca
 * @package   migrate
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class CreateTableDrivers extends Phigrate_Migration_Base
{
    /**
     * up
     *
     * @return void
     */
    public function up()
    {
        $this->createTable('drivers', array(
            'id'      => 'drv_id',
            'options' => 'Engine=InnoDB DEFAULT CHARSET=utf8',
        ))
            // Numéro de licence optionel
            ->column('drv_license', 'string')
            ->column('drv_license_type', 'string')
            ->column('drv_firstname', 'string')
            ->column('drv_lastname', 'string')
            ->column('drv_email', 'string')
            ->column('drv_phone', 'string', array('length' => 15))
            // Un pilote peut ne pas être affilié à un club
            ->column('drv_clb_id', 'integer', array(
                'null'     => true,
                'unsigned' => true,
            ))
            ->column('drv_createdAt', 'timestamp', array(
                'null' => false,
                'default' => '0000-00-00 00:00',
            ))
            ->column('drv_updatedAt', 'timestamp', array(
                'update'  => 'CURRENT_TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ))
            ->finish();
        // Foreign key sur les clubs
        $this->addForeignKey('drivers', 'drv_clb_id', 'clubs', 'clb_id');
    }

    /**
     * down
     *
     * @return void
     */
    public function down()
    {
        $this->removeForeignKey('drivers', 'drv_clb_id', 'clubs', 'clb_id');
        $this->dropTable('drivers');
    }
}