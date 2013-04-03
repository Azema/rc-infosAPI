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
            'options' => 'Engine=InnoDB DEFAULT CHARSET=utf8',
        ))
            // Numéro de licence optionel
            ->column('license', 'string')
            ->column('licenseType', 'string')
            ->column('firstname', 'string')
            ->column('lastname', 'string')
            ->column('email', 'string')
            ->column('phone', 'string', array('length' => 15))
            // Un pilote peut ne pas être affilié à un club
            ->column('clubId', 'integer', array(
                'null'     => true,
                'unsigned' => true,
            ))
            ->column('createdAt', 'timestamp', array(
                'null' => false,
                'default' => '0000-00-00 00:00',
            ))
            ->column('updatedAt', 'timestamp', array(
                'update'  => 'CURRENT_TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ))
            ->finish();
        // Foreign key sur les clubs
        $this->addForeignKey('drivers', 'clubId', 'clubs', 'id');
    }

    /**
     * down
     *
     * @return void
     */
    public function down()
    {
        $this->removeForeignKey('drivers', 'clubId', 'clubs', 'id');
        $this->dropTable('drivers');
    }
}