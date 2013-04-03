<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

/**
 * Simple extension to facilitate the specialized JsonStrategy and JsonRenderer
 * in this Module.
 */
class Rca_View_JsonModel
{
    protected $_payload;

    /**
     * Does the payload represent an API-Problem?
     *
     * @return bool
     */
    public function isApiProblem()
    {
        $payload = $this->getPayload();
        return ($payload instanceof Rca_Restfull_ApiProblem);
    }

    /**
     * Does the payload represent a HAL collection?
     *
     * @return bool
     */
    public function isHalCollection()
    {
        $payload = $this->getPayload();
        return ($payload instanceof Rca_Restfull_HalCollection);
    }

    /**
     * Does the payload represent a HAL item?
     *
     * @return bool
     */
    public function isHalResource()
    {
        $payload = $this->getPayload();
        return ($payload instanceof Rca_Restfull_HalResource);
    }

    /**
     * Set the payload for the response
     *
     * This is the value to represent in the response.
     *
     * @param  mixed $payload
     * @return RestfulJsonModel
     */
    public function setPayload($payload)
    {
        $this->_payload = $payload;
        return $this;
    }

    /**
     * Retrieve the payload for the response
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->_payload;
    }
}
