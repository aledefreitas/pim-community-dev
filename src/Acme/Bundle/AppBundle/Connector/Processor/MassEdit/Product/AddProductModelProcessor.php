<?php

declare(strict_types=1);

namespace Acme\Bundle\AppBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AbstractProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\AddParent;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddProductModelProcessor extends AbstractProcessor
{
    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /** @var SaverInterface */
    private $productModelSaver;

    /** @var AddParent */
    private $addParent;

    /** @var ValidatorInterface */
    private $validator;

    protected $userRepository;

    public function __construct(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productModelSaver,
        AddParent $addParent,
        ValidatorInterface $validator,
        UserRepositoryInterface $userRepository
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelSaver = $productModelSaver;
        $this->addParent = $addParent;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }

    public function process($product)
    {
        if ($product->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            $this->stepExecution->addWarning(
                'The parent of a variant product cannot be changed',
                [],
                new DataInvalidItem($product)
            );
            return ;
        }

        $actions = $this->getConfiguredActions();

        $parentProductModelCode = 'xxx-xyz123';
        $familyVariant = $actions[0]['value'];

        $productModel = $this->getOrCreateProductModel($product, $parentProductModelCode, $familyVariant);

        try {
            $product = $this->addParent->to($product, $parentProductModelCode);
        } catch (\InvalidArgumentException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));
            return ;
        }

        if (!$this->isProductValid($product)) {
            return ;
        }

        return $product;
    }
    
    private function getOrCreateProductModel($product, $code, $familyVariant) {
        $productModel = $this->productModelFactory->create();
        $content = array(
            'code' => $code,
            'family_variant' => $familyVariant
        );

        $categories = array();
        foreach ($product->getCategories() as $category) {
            $categories[] = $category->getCode();
        }
        $content['categories'] = $categories;     

        foreach ($product->getValues() as $productValue) {
            print_r($productValue);
        }

        /*
        $values = array();

        $fieldValues = array();

        $data = 'Novo product model';
        $value = array(
            'locale' => 'en_US',
            'scope' => null,
            'data' => $data
        );
        
        $fieldValues[] = $value;
        
        $values['name'] = $fieldValues;
        
        $content['values'] = $values;
        */


        $this->productModelUpdater->update($productModel, $content);

        $violations = $this->validator->validate($productModel);
        if (count($violations) > 0) {
            //TODO:
            //$normalizedViolations = $this->normalizeCreateViolations($violations, $productModel);

            return null;
        }

        $this->productModelSaver->save($productModel);

        foreach ($product->getValues() as $value) {
            if ($value->getAttributeCode() == 'sku') continue;
            $productModel->addValue($value);
        }
        $this->productModelSaver->save($productModel);

        return $productModel;
    }

    /**
     * Validate the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    private function isProductValid(ProductInterface $product): bool
    {
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }
}