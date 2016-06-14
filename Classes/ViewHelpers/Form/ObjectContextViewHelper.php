<?php

namespace JWeiland\Clubdirectory\ViewHelpers\Form;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Thomas Maroschik <tmaroschik@dfau.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormViewHelper;

/**
 * Form Object context helper. Changes the form object for nested domain objects.
 *
 * = A Form which should render a domain object =
 *
 * <code title="Binding a child domain object to a form">
 * <f:form action="…" name="…" object="{shoppingCart}">
 *   <f:for each="{shoppingCart.items}" as="item">
 *     <t:form.objectContext parentProperty="items" object="{item}">
 *       <f:form.hidden property="id" />
 *       <f:form.textbox property="name" />
 *     </t:form.objectContext>
 *   </f:for>
 * </f:form>
 * </code>
 * This automatically inserts the value of {shoppingCart.[customerUid].customer.name} inside the textbox and adjusts the name of the textbox accordingly.
 */
class ObjectContextViewHelper extends AbstractFormViewHelper
{
    /**
     * @var array
     */
    protected $backupViewHelperVariableContainer;

    /**
     * @var int
     */
    protected $objectPositionInParentCollection;

    /**
     * Initialize arguments.
     *
     * @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parentProperty', 'string', 'Name of parent object property that contains object argument', true);
        $this->registerArgument('object', 'mixed', 'Object to use for the form object context. Use in conjunction with the "property" attribute on the sub tags', true);
    }

    /**
     * Render the object context.
     *
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     *
     * @return string rendered form
     */
    public function render()
    {
        if (!$this->viewHelperVariableContainer->exists('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName')) {
            throw new \OutOfBoundsException('The ObjectContextViewHelper may not be used outside the object acessor mode of a form viewhelper.', 1379072385);
        }
        if (!is_object($this->arguments['object'])) {
            throw new \InvalidArgumentException('The value of the object argument has to be an object.', 1379073736);
        }

        $this->objectPositionInParentCollection = $this->detectObjectPositionInParentCollection();

        $this->addFormObjectNameToViewHelperVariableContainer();
        $this->addFormObjectToViewHelperVariableContainer();

        $content = $this->renderChildren();

        $additionalIdentityProperties = $this->viewHelperVariableContainer->get('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'additionalIdentityProperties');
        $additionalIdentityProperties[$this->getFormObjectName()] = $this->renderHiddenIdentityField($this->arguments['object'], $this->getFormObjectName());
        $this->viewHelperVariableContainer->addOrUpdate('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'additionalIdentityProperties', $additionalIdentityProperties);

        $this->restoreFormObjectInViewHelperVariableContainer();
        $this->restoreFormObjectNameInViewHelperVariableContainer();

        return $content;
    }

    /**
     * @return int
     */
    protected function detectObjectPositionInParentCollection()
    {
        $formObject = $this->viewHelperVariableContainer->get('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject');
        $collection = ObjectAccess::getProperty($formObject, $this->arguments['parentProperty']);
        $position = 0;
        foreach ($collection as $item) {
            if ($item === $this->arguments['object']) {
                return $position;
            }
            ++$position;
        }
    }

    /**
     * Adds the form object name to the ViewHelperVariableContainer if "objectName" argument or "name" attribute is specified.
     */
    protected function addFormObjectNameToViewHelperVariableContainer()
    {
        $this->backupViewHelperVariableContainer['formObjectName'] = $this->viewHelperVariableContainer->get('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName');
        $this->viewHelperVariableContainer->remove('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName');
        $this->viewHelperVariableContainer->add('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName', $this->getFormObjectName());
    }

    /**
     * @return string
     */
    protected function getFormObjectName()
    {
        return $this->backupViewHelperVariableContainer['formObjectName'].'['.$this->arguments['parentProperty'].']['.$this->objectPositionInParentCollection.']';
    }

    /**
     * Removes the form name from the ViewHelperVariableContainer.
     */
    protected function restoreFormObjectNameInViewHelperVariableContainer()
    {
        $this->viewHelperVariableContainer->remove('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName');
        $this->viewHelperVariableContainer->add('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObjectName', $this->backupViewHelperVariableContainer['formObjectName']);
    }

    /**
     * Adds the object that is bound to this form to the ViewHelperVariableContainer if the formObject attribute is specified.
     */
    protected function addFormObjectToViewHelperVariableContainer()
    {
        $this->backupViewHelperVariableContainer['formObject'] = $this->viewHelperVariableContainer->get('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject');
        $this->viewHelperVariableContainer->remove('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject');
        $this->viewHelperVariableContainer->add('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject', $this->arguments['object']);
    }

    /**
     * Removes the form object from the ViewHelperVariableContainer.
     */
    protected function restoreFormObjectInViewHelperVariableContainer()
    {
        $this->viewHelperVariableContainer->remove('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject');
        $this->viewHelperVariableContainer->add('TYPO3\\CMS\\Fluid\\ViewHelpers\\FormViewHelper', 'formObject', $this->backupViewHelperVariableContainer['formObject']);
    }
}
