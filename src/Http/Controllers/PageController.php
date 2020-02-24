<?php namespace WebEd\Base\Pages\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Pages\Http\DataTables\PagesListDataTable;
use WebEd\Base\Pages\Http\Requests\CreatePageRequest;
use WebEd\Base\Pages\Http\Requests\UpdatePageRequest;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;
use Yajra\Datatables\Engines\BaseEngine;

class PageController extends BaseAdminController
{
    protected $module = 'webed-pages';

    /**
     * @param \WebEd\Base\Pages\Repositories\PageRepository $pageRepository
     */
    public function __construct(PageRepositoryContract $pageRepository)
    {
        parent::__construct();

        $this->repository = $pageRepository;

        $this->middleware(function (Request $request, $next) {
            $this->breadcrumbs->addLink(trans('webed-pages::base.page_title'), route('admin::pages.index.get'));

            $this->getDashboardMenu($this->module);

            return $next($request);
        });
    }

    /**
     * Show index page
     * @method GET
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PagesListDataTable $pagesListDataTable)
    {
        $this->setPageTitle(trans('webed-pages::base.page_title'));

        $this->dis['dataTable'] = $pagesListDataTable->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'index.get', $pagesListDataTable)->viewAdmin('index');
    }

    /**
     * @param PagesListDataTable|BaseEngine $pagesListDataTable
     * @return mixed
     */
    public function postListing(PagesListDataTable $pagesListDataTable)
    {
        $data = $pagesListDataTable->with($this->groupAction());

        return do_filter(BASE_FILTER_CONTROLLER, $data, WEBED_PAGES, 'index.post', $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    protected function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['edit-pages'])) {
                return [
                    'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['delete-pages'])) {
                        return [
                            'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete pages
                     */
                    $ids = do_filter(BASE_FILTER_BEFORE_DELETE, $ids, WEBED_PAGES);

                    $result = $this->repository->deletePage($ids);

                    do_action(BASE_ACTION_AFTER_DELETE, WEBED_PAGES, $ids, $result);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ]);
                    break;
                default:
                    return [
                        'customActionMessage' => trans('webed-core::errors.' . \Constants::METHOD_NOT_ALLOWED . '.message'),
                        'customActionStatus' => 'danger'
                    ];
                    break;
            }
            $data['customActionMessage'] = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
            $data['customActionStatus'] = !$result ? 'danger' : 'success';
        }
        return $data;
    }

    /**
     * Update page status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus($id, $status)
    {
        $data = [
            'status' => $status
        ];
        $result = $this->repository->updatePage($id, $data);
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_PAGES, 'create.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('webed-pages::base.form.create_page'));
        $this->breadcrumbs->addLink(trans('webed-pages::base.form.create_page'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'create.get')->viewAdmin('create');
    }

    public function postCreate(CreatePageRequest $request)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_PAGES, 'create.post');

        $data = $this->parseData($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createPage($data);

        do_action(BASE_ACTION_AFTER_CREATE, WEBED_PAGES, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if (!$result) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::pages.edit.get', ['id' => $result]));
        }

        return redirect()->to(route('admin::pages.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-pages::base.form.page_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_PAGES, 'edit.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('webed-pages::base.form.edit_page') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans('webed-pages::base.form.edit_page'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_PAGES, 'edit.get', $id)->viewAdmin('edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdatePageRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-pages::base.form.page_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_PAGES, 'edit.post');

        $data = $this->parseData($request);
        $data['updated_by'] = $this->loggedInUser->id;

        $result = $this->repository->updatePage($item, $data);

        do_action(BASE_ACTION_AFTER_UPDATE, WEBED_PAGES, $id, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if ($this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::pages.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, WEBED_PAGES);

        $result = $this->repository->deletePage($id);

        do_action(BASE_ACTION_AFTER_DELETE, WEBED_PAGES, $id, $result);

        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function parseData(Request $request)
    {
        $data = $request->get('page', []);
        if (!$data['slug']) {
            $data['slug'] = str_slug($data['title']);
        }
        return $data;
    }
}
