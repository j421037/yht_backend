<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('articles', 'ArticleController@index');
// Route::get('articles/{id}', 'ArticleController@show');
// Route::post('articles', 'ArticleController@store');
// Route::put('articles/{id}', 'ArticleController@update');
// Route::delete('articles/{id}', 'ArticleController@delete');
Route::post('auth/register', 'AuthController@register');
// Route::post('auth/login', 'AuthController@login');
Route::post('auth/login', 'AuthController@login');

// Route::group(['middleware' => 'jwt.auth'], function(){
Route::group(['middleware' => 'refresh.token'], function () {
    Route::get('auth/user', 'AuthController@user');
    Route::post('auth/user', 'AuthController@user');
    Route::post('auth/logout', 'AuthController@logout');
    Route::post('auth/put/resetpass', 'AuthController@resetPass');

    /**文件上传**/
    Route::post('file/upload', 'AttachmentController@upload');
    Route::group(['middleware' => 'authority'], function () {
        Route::post('permission/menu', 'PermissionController@getMenu');
        Route::post('permission/add', 'PermissionController@addMenu');
        Route::post('permission/add/group', 'PermissionController@addGroup');
        Route::post('permission/get/groups', 'PermissionController@getGroups');
        Route::post('permission/get/all', 'PermissionController@getAll');
        Route::post('permission/get/all/test', 'PermissionController@test');
        Route::post('permission/put/one', 'PermissionController@update');
        Route::post('permission/delete', 'PermissionController@delete');

        Route::post('role/list', 'RoleController@list');
        Route::post('role/get/permission', 'RoleController@getNode');
        Route::post('role/get/one', 'RoleController@getOne');
        Route::post('role/add', 'RoleController@add');
        Route::post('role/get/NodeSelect', 'RoleController@getNodeSelect');
        Route::post('role/update/user', 'RoleController@updateUser');
        Route::post('role/put/one', 'RoleController@setRole');

        Route::post('user/get/list', 'UserManagerController@getList');
        Route::post('user/get/role', 'UserManagerController@role');
        Route::post('user/put/role', 'UserManagerController@saveRole');
        Route::post('user/update/authorize', 'UserManagerController@UserAllow');
        Route::post('user/update/workwx', 'UserManagerController@updateUserFromWorkwx');
        Route::post('user/get/init', 'UserManagerController@init');
        Route::post('user/put/workwx', 'UserManagerController@joinWorkWx');
        Route::post('user/import', 'AuthController@import');
        Route::post('user/disable', 'AuthController@UserDisable');
        Route::post('user/delete', 'AuthController@UserDelete');

        Route::post('brand/post/one', 'BrandController@store');
        // Route::post('brand/get/all', 'BrandController@all');
        Route::post('brand/put/one', 'BrandController@update');
        Route::post('brand/put/status', 'BrandController@updateStatus');

        Route::post('base/put/url', 'BaseDataController@updateIconUrl');
        Route::post('base/get/all', 'BaseDataController@list');

        Route::post('customer/get/region', 'CustomerReleaseController@region');
        Route::post('customer/post/create', 'CustomerReleaseController@store');
        Route::post('customer/post/update', 'CustomerReleaseController@update');
        Route::post('customer/manager/list', 'CustomerReleaseController@list');
        Route::post('customer/publish', 'CustomerReleaseController@publish');
        Route::post('customer/publish/test', 'CustomerReleaseController@publishTest');

        Route::post('customer/public/list', 'CustomerPubController@list');

        Route::post('customer/public/test', 'CustomerPubController@test');

        Route::post('customer/personal/list', 'MyCustomerController@list');
        Route::Post('customer/receive/one', 'MyCustomerController@store');
        Route::post('customer/post/comments', 'CustomerCommentsController@store');
        Route::post('customer/get/comments', 'CustomerCommentsController@comments');
        Route::post('customer/free/one', 'MyCustomerController@free');
        Route::post('customer/accept/one', 'MyCustomerController@accept');

        Route::post('customer/get/init', 'CustomerController@init');


        Route::post('department/post/create', 'DepartmentController@store');
        Route::post('department/put/one', 'DepartmentController@modify');
        Route::post('department/get/users', 'DepartmentController@user');
        Route::post('department/put/users', 'DepartmentController@updateUser');
        Route::post('department/put/manager', 'DepartmentController@manager');
        /**企业微信管理**/
        Route::post('wechat/post/chat', 'WechatController@storeChat');
        /*** 外部客户注入****************************/
        Route::post('out/customer/list', 'OutCustomerController@list');
        Route::post('out/customer/one', 'OutCustomerController@one');
        Route::post('out/customer/update', 'OutCustomerController@update');
        Route::post('out/customer/delete', 'OutCustomerController@delete');
        Route::post('out/customer/publish', 'OutCustomerController@publish');
        /**文章模块**/
        Route::post('article/post', 'ArticleController@store');
        Route::post('article/get', 'ArticleController@index');
        Route::post('article/portal/get', 'ArticlePortalController@index');
        Route::post('article/modules/get', 'ArtilceModuleController@index');
        Route::post('article/category/get', 'ArticleCategoryController@index');
        Route::post('article/category/get/feelback', 'ArticleCategoryController@feelback');
        Route::post('article/list/category/get', 'ArticleCategoryController@ArticleListCategory');
        Route::post('article/category/manager/get', 'ArticleCategoryController@manager');
        Route::post('article/category/post', 'ArticleCategoryController@store');
        Route::post('article/category/delete', 'ArticleCategoryController@del');
        Route::post('article/get/{id}', 'ArticleController@show');
        Route::post('article/category/change', 'ArticleController@UpdateCategory');
        /**评论模块**/
        Route::post('article/answer/post', 'ArticleAnswerController@store');
        Route::post('article/answer/get', 'ArticleAnswerController@index');
        /**文章点赞**/
        Route::post('article/agree/click', 'ArticleDataController@agreeClick');
        Route::post('article/agree', 'ArticleDataController@agree');
        /**草稿箱**/
        Route::post('article/draft', 'ArticleController@draft');
        /**从草稿箱发布文章**/
        Route::post('article/publish', 'ArticleController@publish');
        Route::post('article/test', 'ArticleController@ArticleQuery');
        /**获取要修改的文章**/
        Route::post('article/edit/one', 'ArticleController@ShowEdit');
        /**更新**/
        Route::post('article/update', 'ArticleController@update');
        /**已发表**/
        Route::post('article/publish/get', 'ArticleController@PublishList');
        //删除文章
        Route::post('article/delete', 'ArticleController@delete');
        //置顶
        Route::post('article/settop', 'ArtilceModuleController@SetTop');
        Route::post('article/canceltop', 'ArtilceModuleController@CancelTop');
        //获取点赞动态
        Route::post('article/notify/agree', 'ArticleNotifyController@agree');
        Route::post('article/notify/answer', 'ArticleNotifyController@answer');
        //文章图集
        Route::post('article/photos/post', 'ArticlePhotoController@store');
        Route::post('article/photos/one', 'ArticlePhotoController@one');
        //论坛公共版块设置
        Route::post('article/module/table', 'ForumModuleController@index');
        Route::post('article/module/store', 'ForumModuleController@store');
        Route::post('article/module/delete', 'ForumModuleController@del');
        Route::post('article/module/update', 'ForumModuleController@update');
        Route::post('article/module/sync', 'ForumModuleController@sync');
        Route::post('article/module/all', 'ForumController@AllModule');
        Route::post('article/module/personal', 'ForumController@PersonalModuleAndCategory');
        //修改文章分类
        Route::post('article/category/put', 'ArticleCategoryController@update');
        /**点赞明细**/
        Route::post('article/agree/get', 'ArticleDataController@AgreeDetail');
        Route::post('article/agree/count', 'ArticleDataController@AgreeCount');
        /****/
        Route::post('article/mine/notify', 'ArticleNotifyController@AllNotify');
        /**新增客户**/
        Route::post('realcustomer/store', 'RealCustomerController@store');
        Route::post('realcustomer/import', 'RealCustomerController@ImportFromExcel');
        Route::post('realcustomer/test', 'RealCustomerController@Test');
        /**修改客户状态**/
        Route::post('realcustomer/update/status', 'RealCustomerController@updateStatus');
        /**新增项目**/
        Route::post('project/store', 'ProjectController@store');
        /**查询项目**/
        Route::post('project/query', 'ProjectController@query');
        /**下载模板**/
        Route::any('realcustomer/template', 'RealCustomerController@downloadTemp');
        /**查询客户**/
        Route::post('realcustomer/query', 'RealCustomerController@query');
        /**应收单**/
        Route::post('receivable/store', 'ReceivableController@store');
        Route::post('receivable/update', 'ReceivableController@update');
        Route::post('receivable/all', 'ReceivableController@all');
        Route::post('receivable/delete', 'ReceivableController@delete');
        /**收款单**/
        Route::post('receivebill/store', 'ReceivebillController@store');
        Route::post('receivebill/all', 'ReceivebillController@all');
        Route::post('receivebill/update', 'ReceivebillController@update');
        Route::post('receivebill/delete', 'ReceivebillController@del');
        /**退货单**/
        Route::post('refund/store', 'RefundController@store');
        Route::post('refund/all', 'RefundController@all');
        Route::post('refund/delete', 'RefundController@del');
        /**更新退货**/
        Route::post('refund/update', 'RefundController@update');
        /**数据汇总查询**/
        Route::post('arsum/query', 'ARSumController@query');
        Route::post('arsum/role', 'ARSumController@role');
        Route::post('artype/store', 'ArTypeController@store');
        Route::post('artype/index', 'ArTypeController@index');
        //同步金蝶销售订单
        Route::post('arsum/sync_kingdee', "ARSumController@SyncKingdeeSaleOrder");
        /**应收表头**/
        Route::post('arsum/filter', 'ARSumFilterController@ARSumFilterTable');
        Route::post('arsum/filter/query', 'ARSumFilterController@FieldQuery');
        /**过滤方案**/
        Route::post('arsum/filter/get/program', 'ARSumFilterController@PersonalFilterProgram');
        //潜在客户
        Route::post('potentialCustomer/all', 'PotentialCustomerController@all');
        Route::post('potentialProject/store', 'PotentialProjectController@store');
        /**收款计划**/
        Route::post('ReceivablePlan/add', 'ReceivablePlanController@store');
        Route::post('ReceivablePlan/all', 'ReceivablePlanController@all');
        Route::post('ReceivablePlan/update', 'ReceivablePlanController@update');
        Route::post('ReceivablePlan/delete', 'ReceivablePlanController@del');
        /**应收后台管理**/
        Route::post('arset/fieldtype', 'ARSetController@FieldType');
        Route::post('arset/fieldstore', 'ARSetController@StoreField');

        /**枚举管理**/
        Route::post('enumberate/store', 'EnumberateController@store');
        Route::post('enumberate/update', 'EnumberateController@update');
        Route::post('enumberate/all', 'EnumberateController@all');
        /**属性绑定**/
        Route::post('bindattr/store', 'BindAttrController@store');
        Route::post('bindattr/all', 'BindAttrController@list');

        Route::post('bindattr/update', 'BindAttrController@update');

        /**调试初始化应收数据**/
        Route::post('arsum/initialization', 'ARSumController@initialization');

        /**产品管理**/
        Route::post("products/store", "ProductCategoryController@store");
        Route::post("products/list", "ProductCategoryController@CategoryList");
        Route::post("products/table/create", "ProductManagerController@store");
        Route::post("products/table/list", "ProductManagerController@PriceTableList");
        Route::post("products/table/delete", "ProductManagerController@PriceTableDelete");
        Route::post("products/prices","ProductPriceController@PriceList");
        Route::post("products/prices/update", "ProductPriceController@update");
        Route::post("products/prices/fastupdate","ProductPriceController@FastUpdate");
        Route::post("products/prices/version","ProductPriceController@PriceVersionList");
        /**单品牌报价**/
        Route::post("products/params","ProductMakeOfferController@params");
        Route::post("products/offers","ProductMakeOfferController@OfferList");
        Route::post("products/offer/store", "ProductMakeOfferController@store");
    });
    /**不需要验证权限**/
    Route::post('bindattr/one', 'BindAttrController@one');
    Route::post('brand/get/all', 'BrandController@all');
    Route::post('user/get/navigation', 'UserManagerController@navigation');
    Route::post('user/yht', 'YhtUserController@user');
    Route::post('m/get/basedata', 'MobileBaseController@init');
    Route::post('department/get/list', 'DepartmentController@list');
    /*********************Editor**************************/
    Route::any('editor/uploadimage', 'EditorController@UploadImage');
    Route::any('editor/listimage', 'EditorController@listImage');
    /***论坛基础***/
    Route::post('forumenu/get', 'ForumController@menu');
    /**过滤方案**/
    Route::post('arsum/filter/create/program', 'FilterProgramController@store');
    Route::post('arsum/filter/update/program', 'FilterProgramController@updateName');
    Route::post('arsum/filter/delete/program', 'FilterProgramController@del');
    Route::post('arsum/filter/update/config', 'FilterProgramController@updateConfig');
});
Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('auth/refresh', 'AuthController@refresh');
});

Route::post('base/get/icon', 'BaseDataController@getIcon');
Route::post('base/pagination', 'BaseDataController@pagination');
/*****************外部客户注入功能模块*************************/
Route::post('out/initCapt', 'OutCustomerController@initCapt');
Route::post('out/auth/login', 'AuthController@OutLogin');
Route::post('out/auth/signup', 'AuthController@OutSign');
