<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Transform a Vivapet Manufacturer Product structure to a Akeneo Product structure
 *
 *
 * @author    Diogo Pina <diogo@vivapets.com>
 * @copyright 2015 Akeneo SAS (http://www.vivapets.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTransformer
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;


    /** @var SimpleFactoryInterface */
    protected $optionFactory;

    /** @var SaverInterface */
    protected $optionSaver;

    /**
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param LocaleRepositoryInterface           $localeRepository
     * @param SimpleFactoryInterface              $optionFactory
     * @param SaverInterface                      $optionSaver
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        SimpleFactoryInterface $optionFactory,
        SaverInterface $optionSaver
    ) {        
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->optionFactory = $optionFactory;
        $this->optionSaver = $optionSaver;
    }
    
    public function transform(array $item, array $options = []): array
    {
        if (!$this->isVivapetsItem($item)) {
            return $item;
        }

        $item = $this->fixcolumns($item);
        $this->convertValues($item);
        $this->createUrlKey($item);

        return $item;
    }

    private function isVivapetsItem($item) :bool
    {
        if (isset($item['SKU']) || isset($item['Product Model'])) {
            return true;
        }
        return false;
    }

    private function fixcolumns($item) :array
    {
        $newItem = array();
        foreach ($item as $column => $value) {
            $column = strtolower($column);

            if ($column == 'name' || $column == 'product_name' || $column == 'product name') $column = 'name-en_US';
            if ($column == 'ean') $column = 'ean_code-en_US';
            if ($column == 'price') $column = 'price-EUR';
            $newItem[$column] = $value;
        }

        if (!isset($newItem['family'])) $newItem['family'] = 'products';

        return $newItem;
    }

    private function convertValues(&$item) :void
    {
        if (isset($item['manufacturer'])) {
            $item['manufacturer'] = $this->convertValue('manufacturer', $item['manufacturer']);
        }
        elseif (isset($item['url_key'])) {
            $item['url_key'] = '';
        }
    }

    private function convertValue($column, $value) :string {
        $attribute = $this->attributeRepository->findOneByIdentifier($column);

        $newValue = false;
        
        $options = $attribute->getOptions();           
        foreach ($options as $option) {
            foreach ($option->getOptionValues() as $optionValue) {
                if ($optionValue->getValue() == $value) {
                    $newValue = $option->getCode();
                    break;
                }
            }
            if ($newValue) break;
        }
        if ($newValue) {
            $value = $newValue; 
        }
        else {
            $code = $this->slugify($value);

            $attributeOption = $this->optionFactory->create();
            $attributeOption->setAttribute($attribute);
            $attributeOption->setCode($code);

            $locales = $this->localeRepository->getActivatedLocales();
            foreach ($locales as $locale) {
                $optionValue = new AttributeOptionValue();
                $optionValue->setLocale($locale->getCode());
                $optionValue->setValue($value);
                $attributeOption->addOptionValue($optionValue);
            }

            $this->optionSaver->save($attributeOption);

            $value = $code;
        }

        return $value;
    }

    private function createUrlKey(&$item) {
        $urlKey = $this->slugify($item['name-en_US']);

        $locales = $this->localeRepository->getActivatedLocales();
        foreach ($locales as $locale) {
            $item['url_key-' . $locale->getCode()] = $urlKey;
        }
    }

    private function slugify($string) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
}