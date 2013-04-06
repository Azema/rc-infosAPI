<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

/**
 * Handles rendering of the following:
 *
 * - API-Problem
 * - HAL collections
 * - HAL resources
 */
class Rca_View_JsonRenderer
{
    /**
     * @var ApiProblem
     */
    protected $apiProblem;

    /**
     * Whether or not to render exception stack traces in API-Problem payloads
     *
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * @var HelperPluginManager
     */
    protected $helpers;

    public static $halLinks;

    /**
     * Set display_exceptions flag
     *
     * @param  bool $flag
     * @return RestfulJsonRenderer
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool)$flag;
        return $this;
    }

    /**
     * Whether or not what was rendered represents an API problem
     *
     * @return bool
     */
    public function isApiProblem()
    {
        return (null !== $this->apiProblem);
    }

    /**
     * @return null|ApiProblem
     */
    public function getApiProblem()
    {
        return $this->apiProblem;
    }

    /**
     * Render a view model
     *
     * If the view model is a RestfulJsonRenderer, determines if it represents
     * an ApiProblem, HalCollection, or HalResource, and, if so, creates a custom
     * representation appropriate to the type.
     *
     * If not, it passes control to the parent to render.
     *
     * @param  mixed $nameOrModel
     * @param  mixed $values
     * @return string
     */
    public function render($nameOrModel, $values = null)
    {
        $this->apiProblem = null;

        if (!$nameOrModel instanceof Rca_View_JsonModel) {
            return json_encode($nameOrModel);
        }

        if ($nameOrModel->isApiProblem()) {
            return $this->renderApiProblem($nameOrModel->getPayload());
        }

        $helper = self::$halLinks;
        if ($nameOrModel->isHalResource()) {
            $payload = $helper->renderResource($nameOrModel->getPayload());
            return json_encode($payload);
        }

        if ($nameOrModel->isHalCollection()) {
            $payload = $helper->renderCollection($nameOrModel->getPayload());
            if ($payload instanceof Rca_Restfull_ApiProblem) {
                return $this->renderApiProblem($payload);
            }
            return json_encode($payload);
        }

        return json_encode($nameOrModel);
    }

    /**
     * Render an API Problem representation
     *
     * Also sets the $apiProblem member to the passed object.
     *
     * @param  ApiProblem $apiProblem
     * @return string
     */
    protected function renderApiProblem(Rca_Restfull_ApiProblem $apiProblem)
    {
        $this->apiProblem   = $apiProblem;
        if ($this->displayExceptions) {
            $apiProblem->setDetailIncludesStackTrace(true);
        }
        return json_encode($apiProblem->toArray());
    }

    /**
     * Inject the helper manager with the HalLinks helper
     *
     * @param  HelperPluginManager $helpers
     */
    protected function injectHalLinksHelper($helpers)
    {
        $helper = new Rca_View_Helper_HalLinks();
        $helper->setView($this);
        $helper->setServerUrlHelper(Zend_Controller_Action_HelperBroker::getExistingHelper('ServerUrl'));
        $helper->setUrlHelper($helpers->getExistingHelper('Url'));
        $helpers->setService('HalLinks', $helper);
    }
}
