<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller\Index;

class UpdateItemOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var \Magento\Store\App\Response\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $this->productRepository = $this->getMock('Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->wishlistProvider = $this->getMock('Magento\Wishlist\Controller\WishlistProvider', [], [], '', false);
        $this->view = $this->getMock('Magento\Framework\App\View', [], [], '', false);
        $this->redirect = $this->getMock('\Magento\Store\App\Response\Redirect', [], [], '', false);
        $this->om = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $this->messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->url = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->eventManager = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
    }

    public function tearDown()
    {
        unset(
            $this->productRepository,
            $this->context,
            $this->request,
            $this->response,
            $this->wishlistProvider,
            $this->view,
            $this->redirect,
            $this->om,
            $this->messageManager,
            $this->url,
            $this->eventManager
        );
    }

    public function prepareContext()
    {
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->om);
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context
            ->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);
        $this->context
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->url);
        $this->context
            ->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($actionFlag);
        $this->context
            ->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $this->context
            ->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    protected function getController()
    {
        $this->prepareContext();
        return new \Magento\Wishlist\Controller\Index\UpdateItemOptions(
            $this->context,
            $this->customerSession,
            $this->wishlistProvider,
            $this->productRepository
        );
    }

    public function testExecuteWithoutProductId()
    {
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('product')
            ->willReturn(null);

        $this->redirect
            ->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/', [])
            ->willReturn(true);

        $this->getController()->execute();
    }

    public function testExecuteWithoutProduct()
    {
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('product')
            ->willReturn(2);

        $exception = $this->getMock('Magento\Framework\Exception\NoSuchEntityException', [], [], '', false);
        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willThrowException($exception);

        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t specify a product.')
            ->willReturn(true);

        $this->redirect
            ->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/', [])
            ->willReturn(true);

        $this->getController()->execute();
    }

    public function testExecuteWithoutWishList()
    {
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $item = $this->getMock('Magento\Wishlist\Model\Item', [], [], '', false);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $this->messageManager
            ->expects($this->never())
            ->method('addError')
            ->with('We can\'t specify a product.')
            ->willReturn(true);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn(null);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Wishlist\Model\Item')
            ->willReturn($item);

        $this->redirect
            ->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/', [])
            ->willReturn(true);

        $this->getController()->execute();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessException()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', [], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $item = $this->getMock('Magento\Wishlist\Model\Item', [], [], '', false);
        $helper = $this->getMock('Magento\Wishlist\Helper\Data', [], [], '', false);

        $helper
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(true);

        $wishlist
            ->expects($this->once())
            ->method('getItem')
            ->with(3)
            ->willReturn($item);
        $wishlist
            ->expects($this->once())
            ->method('updateItem')
            ->with(3, new \Magento\Framework\Object([]))
            ->willReturnSelf();
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willReturn(null);
        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->willReturn(56);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);
        $product
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Test name');

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Wishlist\Model\Item')
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->exactly(2))
            ->method('get')
            ->with('Magento\Wishlist\Helper\Data')
            ->willReturn($helper);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $exception = $this->getMock('Magento\Framework\Model\Exception', [], [], '', false);
        $this->messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->with('Test name has been updated in your wish list.', null)
            ->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('', null)
            ->willReturn(true);

        $this->redirect
            ->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/*', ['wishlist_id' => 56])
            ->willReturn(true);

        $this->getController()->execute();
    }
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessCriticalException()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', [], [], '', false);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $item = $this->getMock('Magento\Wishlist\Model\Item', [], [], '', false);
        $helper = $this->getMock('Magento\Wishlist\Helper\Data', [], [], '', false);
        $logger = $this->getMock('Magento\Framework\Logger\Monolog', [], [], '', false);
        $exception = $this->getMock('Exception', [], [], '', false);

        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturn(true);

        $helper
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(true);

        $wishlist
            ->expects($this->once())
            ->method('getItem')
            ->with(3)
            ->willReturn($item);
        $wishlist
            ->expects($this->once())
            ->method('updateItem')
            ->with(3, new \Magento\Framework\Object([]))
            ->willReturnSelf();
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willReturn(null);
        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->willReturn(56);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);
        $product
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Test name');

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with('Magento\Wishlist\Model\Item')
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->at(1))
            ->method('get')
            ->with('Magento\Wishlist\Helper\Data')
            ->willReturn($helper);
        $this->om
            ->expects($this->at(2))
            ->method('get')
            ->with('Magento\Wishlist\Helper\Data')
            ->willReturn($helper);
        $this->om
            ->expects($this->at(3))
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->willReturn($logger);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->with('Test name has been updated in your wish list.', null)
            ->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('An error occurred while updating wish list.', null)
            ->willReturn(true);

        $this->redirect
            ->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/*', ['wishlist_id' => 56])
            ->willReturn(true);

        $this->getController()->execute();
    }
}
