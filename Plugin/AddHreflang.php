<?php

namespace Scandi\StoreHreflangScope\Plugin;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Helper\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddHreflang
 * @package Scandi\StoreHreflangScope\Plugin
 */
class AddHreflang
{
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PageRepositoryInterface
     */
    protected $_pageRepositoryInterface;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * AddHreflang constructor.
     * @param Resolver $resolver
     * @param StoreManagerInterface $storeManager
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Resolver $resolver,
        StoreManagerInterface $storeManager,
        PageRepositoryInterface $pageRepositoryInterface,
        PageFactory $pageFactory
    ) {
        $this->resolver = $resolver;
        $this->_storeManager = $storeManager;
        $this->_pageRepositoryInterface = $pageRepositoryInterface;
        $this->_pageFactory = $pageFactory;
    }

    /**
     * @param Page $subject
     * @param callable $proceed
     * @param $index
     * @param $pageId
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundPrepareResultPage(Page $subject, callable $proceed, $index, $pageId)
    {
        $result = $proceed($index, $pageId);
        $pageStores = $this->_pageRepositoryInterface->getById($pageId)->getStoreId();

        if (count($pageStores) > 1) {
            $this->addMetaHreflang($pageStores, $result, $pageId);
        }

        return $result;
    }

    /**
     * For each store where the page is visible, creates a hreflang
     * @param $pageStores
     * @param $result
     * @param $pageId
     * @throws NoSuchEntityException
     */
    protected function addMetaHreflang($pageStores, $result, $pageId)
    {
        $storeLocales = $this->getStoreLocale($pageStores);
        $pageResult = $result->getConfig();

        foreach ($storeLocales as $storeId => $locale) {
            $pageResult->addRemotePageAsset(
                $this->getPageUrlByStore($storeId, $pageId),
                'alternate',
                ['attributes' => ['rel' => 'alternate', 'hreflang' => $locale]]
            );
        }
    }

    /**
     * Get base url from store and concatenate with actual page url
     * @param $storeId
     * @param $pageId
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getPageUrlByStore($storeId, $pageId)
    {
        $page = $this->_pageFactory->create();
        $page->setStoreId($storeId);
        $page->load($pageId);

        $baseUrl = $this->_storeManager->getStore($storeId)->getBaseUrl();
        $pageUrl = $page->getIdentifier();

        return $baseUrl . $pageUrl;
    }

    /**
     * Creates an array with locale of each store
     * @param $pageStores
     * @return array
     */
    protected function getStoreLocale($pageStores)
    {
        $storeLocales = [];
        foreach ($pageStores as $store) {
            $storeLocales[$store] = $this->normalizeLocale($this->resolver->emulate($store));
        }
        return $storeLocales;
    }

    /**
     * Changes the locale format to hreflang pattern
     * @param $locale
     * @return string
     */
    protected function normalizeLocale($locale)
    {
        return strtolower(str_replace('_', '-', $locale));
    }
}
