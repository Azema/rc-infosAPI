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
 * Class migration DB CreateTableCircuits
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
class CreateTableTracks extends Phigrate_Migration_Base
{
    /**
     * up
     *
     * @return void
     */
    public function up()
    {
        $this->createTable(
            'tracks', array(
                'options' => 'Engine=InnoDB DEFAULT CHARSET=utf8'
            )
        )
            ->column('clubId', 'integer', array(
                'null'     => false,
                'unsigned' => true,
            ))
            ->column('type', 'string', array('length' => 10))
            ->column('motors', 'string')
            ->column('coating', 'string')
            ->column('length', 'smallinteger')
            ->column('scale', 'smallinteger')
            ->column('scales', 'string')
            ->column('address', 'string')
            ->column('postCode', 'string', array('length' => 10))
            ->column('city', 'string')
            ->column('gps', 'string')
            ->column('equipments', 'string')
            ->column('createdAt', 'timestamp', array(
                'null' => false,
                'default' => '0000-00-00 00:00',
            ))
            ->column('updatedAt', 'timestamp', array(
                'update'  => 'CURRENT_TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ))
            ->finish();
        $this->addForeignKey('tracks', 'clubId', 'clubs', 'id');
    }

    /**
     * down
     *
     * @return void
     */
    public function down()
    {
        $this->removeForeignKey('tracks', 'clubId', 'clubs', 'id');
        $this->dropTable('tracks');
    }
}