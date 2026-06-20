<?php
/**
 * Web Routes
 */

use Core\Router;
use Controllers\AuthController;
use Controllers\DashboardController;
use Controllers\UserController;
use Controllers\RoleController;
use Controllers\PipelineController;
use Controllers\DealController;
use Controllers\ContactController;
use Controllers\PaymentController;
use Controllers\SmsController;
use Controllers\ReportController;
use Controllers\SettingController;
use Controllers\DatabaseRepairController;
use Controllers\CustomFieldController;
use Controllers\CategoryController;
use Controllers\SourceController;
use Controllers\LossReasonController;

// Auth routes
Router::get('/login', [AuthController::class, 'loginForm']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);

// Authenticated routes
Router::group('/dashboard', function() {
    Router::get('', [DashboardController::class, 'index']);
});

// Users management (admin only)
Router::group('/users', function() {
    Router::get('', [UserController::class, 'index'], 'users.manage');
    Router::get('/create', [UserController::class, 'create'], 'users.manage');
    Router::post('/store', [UserController::class, 'store'], 'users.manage');
    Router::get('/edit/{id}', [UserController::class, 'edit'], 'users.manage');
    Router::post('/update/{id}', [UserController::class, 'update'], 'users.manage');
    Router::post('/delete/{id}', [UserController::class, 'delete'], 'users.manage');
});

// Roles management (admin only)
Router::group('/roles', function() {
    Router::get('', [RoleController::class, 'index'], 'roles.manage');
    Router::get('/create', [RoleController::class, 'create'], 'roles.manage');
    Router::post('/store', [RoleController::class, 'store'], 'roles.manage');
    Router::get('/edit/{id}', [RoleController::class, 'edit'], 'roles.manage');
    Router::post('/update/{id}', [RoleController::class, 'update'], 'roles.manage');
    Router::post('/delete/{id}', [RoleController::class, 'delete'], 'roles.manage');
});

// Pipelines management
Router::group('/pipelines', function() {
    Router::get('', [PipelineController::class, 'index'], 'pipelines.view');
    Router::get('/create', [PipelineController::class, 'create'], 'pipelines.create');
    Router::post('/store', [PipelineController::class, 'store'], 'pipelines.create');
    Router::get('/edit/{id}', [PipelineController::class, 'edit'], 'pipelines.edit');
    Router::post('/update/{id}', [PipelineController::class, 'update'], 'pipelines.edit');
    Router::post('/delete/{id}', [PipelineController::class, 'delete'], 'pipelines.delete');
    Router::get('/kanban/{id}', [PipelineController::class, 'kanban'], 'pipelines.view');
    Router::post('/update-stage', [PipelineController::class, 'updateStage'], 'pipelines.edit');
    // Stage management (AJAX)
    Router::get('/{id}/stages', [PipelineController::class, 'stages'], 'pipelines.view');
    Router::post('/{id}/stages/store', [PipelineController::class, 'storeStage'], 'pipelines.edit');
    Router::post('/stages/update/{id}', [PipelineController::class, 'updateStageName'], 'pipelines.edit');
    Router::post('/stages/delete/{id}', [PipelineController::class, 'deleteStage'], 'pipelines.delete');
    Router::post('/{id}/stages/reorder', [PipelineController::class, 'reorderStages'], 'pipelines.edit');
});

// Deals management
Router::group('/deals', function() {
    Router::get('', [DealController::class, 'index'], 'deals.view');
    Router::get('/create', [DealController::class, 'create'], 'deals.create');
    Router::post('/store', [DealController::class, 'store'], 'deals.create');
    Router::get('/view/{id}', [DealController::class, 'view'], 'deals.view');
    Router::get('/edit/{id}', [DealController::class, 'edit'], 'deals.edit');
    Router::post('/update/{id}', [DealController::class, 'update'], 'deals.edit');
    Router::post('/delete/{id}', [DealController::class, 'delete'], 'deals.delete');
    Router::post('/add-activity/{id}', [DealController::class, 'addActivity'], 'deals.edit');
    Router::post('/convert/{id}', [DealController::class, 'convertToDeal'], 'deals.edit');
    Router::get('/get-data/{id}', [DealController::class, 'getData'], 'deals.view');
    Router::get('/tags', [DealController::class, 'allTags'], 'deals.view');
    Router::get('/tag/{tag}', [DealController::class, 'byTag'], 'deals.view');
});

// Contacts management
Router::group('/contacts', function() {
    Router::get('', [ContactController::class, 'index'], 'contacts.view');
    Router::get('/create', [ContactController::class, 'create'], 'contacts.create');
    Router::post('/store', [ContactController::class, 'store'], 'contacts.create');
    Router::get('/view/{id}', [ContactController::class, 'view'], 'contacts.view');
    Router::get('/edit/{id}', [ContactController::class, 'edit'], 'contacts.edit');
    Router::post('/update/{id}', [ContactController::class, 'update'], 'contacts.edit');
    Router::post('/delete/{id}', [ContactController::class, 'delete'], 'contacts.delete');
});

// Payment routes
Router::group('/payment', function() {
    Router::get('/create/{deal_id}', [PaymentController::class, 'create'], 'payments.create');
    Router::post('/request', [PaymentController::class, 'requestPayment'], 'payments.create');
    Router::get('/verify', [PaymentController::class, 'verify']);
    Router::post('/callback', [PaymentController::class, 'callback']);
    Router::get('/inquiry/{track_id}', [PaymentController::class, 'inquiry'], 'payments.view');
    Router::get('/history', [PaymentController::class, 'history'], 'payments.view');
});

// Public payment routes (no auth required)
Router::get('/pay/{token}', [PaymentController::class, 'publicPayPage']);
Router::get('/p/{token}', [PaymentController::class, 'publicPayPage']); // Short URL
Router::post('/pay/submit', [PaymentController::class, 'publicSubmit']);
Router::get('/payment/result', [PaymentController::class, 'publicVerifyResult']);

// SMS routes
Router::group('/sms', function() {
    Router::get('/send/{deal_id}', [SmsController::class, 'showSendForm'], 'sms.send');
    Router::post('/send', [SmsController::class, 'send'], 'sms.send');
    Router::post('/send-bulk', [SmsController::class, 'sendBulk'], 'sms.send');
    Router::get('/history', [SmsController::class, 'history'], 'sms.send');
});

// Activities
Router::group('/activities', function() {
    Router::get('', [ReportController::class, 'activities'], 'reports.view');
    Router::post('/toggle-done/{id}', [ReportController::class, 'toggleActivity'], 'reports.view');
});

// Reports
Router::group('/reports', function() {
    Router::get('', [ReportController::class, 'index'], 'reports.view');
    Router::get('/sales', [ReportController::class, 'sales'], 'reports.view');
    Router::get('/pipeline', [ReportController::class, 'pipeline'], 'reports.view');
    Router::get('/contacts', [ReportController::class, 'contacts'], 'reports.view');
});

// Settings
Router::group('/settings', function() {
    Router::get('', [SettingController::class, 'index'], 'settings.manage');
    Router::post('/update', [SettingController::class, 'update'], 'settings.manage');
    Router::post('/toggle-feature', [SettingController::class, 'toggleFeature'], 'settings.manage');
});

// Database Repair & Error Logs
Router::group('/database', function() {
    Router::get('/repair', [DatabaseRepairController::class, 'index'], 'settings.manage');
    Router::post('/repair/run', [DatabaseRepairController::class, 'runRepair'], 'settings.manage');
    Router::get('/error-logs', [DatabaseRepairController::class, 'errorLogs'], 'settings.manage');
});

// Custom Fields
Router::group('/custom-fields', function() {
    Router::get('', [CustomFieldController::class, 'index'], 'settings.manage');
    Router::post('/store', [CustomFieldController::class, 'store'], 'settings.manage');
    Router::post('/update/{id}', [CustomFieldController::class, 'update'], 'settings.manage');
    Router::post('/delete/{id}', [CustomFieldController::class, 'delete'], 'settings.manage');
});

// Contact Categories Management
Router::group('/settings/categories', function() {
    Router::get('', [CategoryController::class, 'index'], 'settings.manage');
    Router::post('/store', [CategoryController::class, 'store'], 'settings.manage');
    Router::post('/update/{id}', [CategoryController::class, 'update'], 'settings.manage');
    Router::post('/delete/{id}', [CategoryController::class, 'delete'], 'settings.manage');
    Router::get('/api', [CategoryController::class, 'getCategories']);
});

// Deal Sources Management
Router::group('/settings/sources', function() {
    Router::get('', [SourceController::class, 'index'], 'settings.manage');
    Router::post('/store', [SourceController::class, 'store'], 'settings.manage');
    Router::post('/update', [SourceController::class, 'update'], 'settings.manage');
    Router::post('/delete', [SourceController::class, 'delete'], 'settings.manage');
    Router::get('/active', [SourceController::class, 'getActive']);
});

// Deal Loss Reasons Management
Router::group('/settings/loss-reasons', function() {
    Router::get('', [LossReasonController::class, 'index'], 'settings.manage');
    Router::post('/store', [LossReasonController::class, 'store'], 'settings.manage');
    Router::post('/update', [LossReasonController::class, 'update'], 'settings.manage');
    Router::post('/delete', [LossReasonController::class, 'delete'], 'settings.manage');
    Router::get('/active', [LossReasonController::class, 'getActive']);
});
