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
 * Class migration DB CreateTableLeagues
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
class CreateTableLeagues extends Phigrate_Migration_Base
{
    /**
     * up
     *
     * @return void
     */
    public function up()
    {
        $this->createTable('leagues', array(
            'id'      => 'leg_id',
            'options' => 'Engine=InnoDB DEFAULT CHARSET=utf8',
        ))
            ->column('leg_name', 'string')
            ->column('leg_president', 'string')
            ->column('leg_address', 'string')
            ->column('leg_postCode', 'string', array('length' => 10))
            ->column('leg_city', 'string')
            ->column('leg_email', 'string')
            ->column('leg_phone', 'string', array('length' => 15))
            ->column('leg_siteWeb', 'string')
            ->column('leg_createdAt', 'timestamp', array(
                'null' => false,
                'default' => '0000-00-00 00:00',
            ))
            ->column('leg_updatedAt', 'timestamp', array(
                'update'  => 'CURRENT_TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ))
            ->finish();
        // Ajout de la relation entre les clubs et les ligues
        $this->addColumn('clubs', 'clb_leg_id', 'integer', array(
            'null'     => false,
            'unsigned' => true,
        ));
        $this->addForeignKey('clubs', 'clb_leg_id', 'leagues', 'leg_id');
    }

    /**
     * down
     *
     * @return void
     */
    public function down()
    {
        $this->removeForeignKey('clubs', 'clb_leg_id', 'leagues', 'leg_id');
        $this->removeColumn('clubs', 'clb_leg_id');
        $this->dropTable('leagues');
    }
}