<?php namespace WebEd\Base\Pages\Http\Middleware;

use \Closure;
use WebEd\Base\Pages\Criterias\Filters\FilterPagesCriteria;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;
use WebEd\Base\Pages\Repositories\PageRepository;

class BootstrapModuleMiddleware
{
    public function __construct()
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  array|string $params
     * @return mixed
     */
    public function handle($request, Closure $next, ...$params)
    {
        admin_bar()->registerLink('Page', route('admin::pages.create.get'), 'add-new');

        $this->registerMenu();
        $this->registerMenuDashboard();
        $this->registerSettings();

        admin_quick_link()->register('page', [
            'title' => trans('webed-pages::base.page'),
            'url' => route('admin::pages.create.get'),
            'icon' => 'icon-notebook',
        ]);

        return $next($request);
    }

    protected function registerMenuDashboard()
    {
        /**
         * Register to dashboard menu
         */
        dashboard_menu()->registerItem([
            'id' => 'webed-pages',
            'priority' => 1,
            'parent_id' => null,
            'heading' => trans('webed-pages::base.admin_menu.pages.heading'),
            'title' => trans('webed-pages::base.admin_menu.pages.title'),
            'font_icon' => 'icon-notebook',
            'link' => route('admin::pages.index.get'),
            'css_class' => null,
            'permissions' => ['view-pages'],
        ]);
    }

    protected function registerMenu()
    {
        /**
         * Register menu widget
         */
        menus_management()->registerWidget(trans('webed-pages::base.admin_menu.pages.title'), 'page', function () {
            $repository = app(PageRepositoryContract::class)
                ->pushCriteria(new FilterPagesCriteria([
                    'status' => 'activated'
                ], [
                    'order' => 'ASC'
                ]))
                ->get();
            $pages = [];
            foreach ($repository as $page) {
                $pages[] = [
                    'id' => $page->id,
                    'title' => $page->title,
                ];
            }
            return $pages;
        });

        /**
         * Register menu link type
         */
        menus_management()->registerLinkType('page', function ($id) {
            $page = app(PageRepositoryContract::class)
                ->findWhere([
                    'status' => 'activated',
                    'id' => $id,
                ]);
            if (!$page) {
                return null;
            }
            return [
                'model_title' => $page->title,
                'url' => get_page_link($page),
            ];
        });
    }

    protected function registerSettings()
    {
        cms_settings()
            ->addSettingField('default_homepage', [
                'group' => 'basic',
                'type' => 'select',
                'priority' => 0,
                'label' => trans('webed-pages::base.settings.default_homepage.label'),
                'helper' => trans('webed-pages::base.settings.default_homepage.helper')
            ], function () {
                /**
                 * @var PageRepository $pageRepo
                 */
                $pageRepo = app(PageRepositoryContract::class);

                $pages = $pageRepo
                    ->pushCriteria(new FilterPagesCriteria([
                        'status' => 'activated'
                    ], [
                        'order' => 'ASC'
                    ]))
                    ->get();

                $pagesArr = [];

                foreach ($pages as $page) {
                    $pagesArr[$page->id] = $page->title;
                }

                return [
                    'default_homepage',
                    $pagesArr,
                    get_setting('default_homepage'),
                    ['class' => 'form-control']
                ];
            });
    }
}
