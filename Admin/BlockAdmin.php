<?php
/*
 * This file is part of the Presta Bundle project.
 *
 * @author Nicolas Bastien <nbastien@prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Presta\CMSCoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin as BaseAdmin;

/**
 * Admin definition for the Site class
 *
 * @package    Presta
 * @subpackage CMSCoreBundle
 * @author     Nicolas Bastien <nbastien@prestaconcept.net>
 */
class BlockAdmin extends BaseAdmin
{
    /**
     * The translation domain to be used to translate messages
     *
     * @var string
     */
    protected $translationDomain = 'PrestaCMSCoreBundle';

    /**
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $blockManager
     */
    public function setBlockManager(BlockServiceManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    protected function getDocumentManager()
    {
        return $this->modelManager->getDocumentManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', 'text')
            ->add('type', 'text')
            ->add('settings', 'array')
            ->add('isActive', 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $block = $this->getSubject();
        $service = $this->blockManager->get($block);

        if ($block->getId() > 0) {
            $service->buildEditForm($formMapper, $block);
        } else {
            $service->buildCreateForm($formMapper, $block);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $block)
    {
        return $this->blockManager->validate($errorElement, $block);
        //Sonata code todo remove ? !
        if ($this->inValidate) {
            return;
        }

        // As block can be nested, we only need to validate the main block, no the children
        $this->inValidate = true;
        $this->blockManager->validate($errorElement, $block);
        $this->inValidate = false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl($name, array $parameters = array(), $absolute = false)
    {
        if ($name == 'create' || $name == 'edit') {
            $parameters = $parameters + array('locale' => $this->getRequest()->get('locale'));
        }

        return parent::generateUrl($name, $parameters, $absolute);
    }

    /**
     * Refresh object to load locale get in param
     *
     * @param   $id
     * @return  $subject
     */
    public function getObject($id)
    {
        $locale = $this->getRequest()->get('locale');
        $block = $this->getDocumentManager()->findTranslation($this->getClass(), $id, $locale);
        if (!is_null($locale)) {
            //Here we have to consider the PHPCR fallback system and rest the locale
            //in case translation does not exist
            $this->getDocumentManager()->bindTranslation($block, $locale);
        }
        $service = $this->blockManager->get($block);
        $service->load($block);

        return $block;
    }
        /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $block = $this->getSubject();
        $service = $this->blockManager->get($block);
        $service->preUpdate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $block = $this->getSubject();
        $service = $this->blockManager->get($block);
        $service->prePersist($object);
    }
}
