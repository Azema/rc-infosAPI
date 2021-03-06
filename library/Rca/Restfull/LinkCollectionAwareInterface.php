<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

interface Rca_Restfull_LinkCollectionAwareInterface
{
    public function setLinks(Rca_Restfull_LinkCollection $links);
    public function getLinks();
}
