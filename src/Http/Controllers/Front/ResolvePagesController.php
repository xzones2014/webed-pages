<?php namespace WebEd\Base\Pages\Http\Controllers\Front;

use WebEd\Base\Http\Controllers\BaseFrontController;
use WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;
use WebEd\Base\Pages\Repositories\PageRepository;

class ResolvePagesController extends BaseFrontController
{
    /**
     * @var PageRepositoryContract|PageRepository
     */
    protected $repository;

    /**
     * SlugWithoutSuffixController constructor.
     * @param PageRepository $repository
     */
    public function __construct(PageRepositoryContract $repository)
    {
        parent::__construct();

        $this->themeController = themes_management()->getThemeController('Pages\Page');

        if (!$this->themeController) {
            echo 'You need to active a theme';
            die();
        }

        if (is_string($this->themeController)) {
            echo 'Class ' . $this->themeController . ' not exists';
            die();
        }

        $this->repository = $repository;
    }

    public function handle($slug = null)
    {
        if(!$slug) {
            $page = $this->repository
                ->findWhere([
                    'id' => do_filter('front.default-homepage.get', get_setting('default_homepage')),
                    'status' => 'activated'
                ]);
        } else {
            $page = $this->repository
                ->findWhere([
                    'slug' => $slug,
                    'status' => 'activated'
                ]);
        }

        if(!$page) {
            if ($slug === null) {
                echo '<h2>You need to setup your default homepage. Create a page then go through to Admin Dashboard -> Configuration -> Settings</h2>';
                die();
            } else {
                abort(404);
            }
        }

        $page = do_filter('front.web.resolve-pages.get', $page);

        /**
         * Update view count
         */
        increase_view_count($page, $page->id);

        seo()
            ->metaDescription($page->description)
            ->metaImage($page->thumbnail)
            ->metaKeywords($page->keywords)
            ->setModelObject($page);

        admin_bar()->registerLink('Edit this page', route('admin::pages.edit.get', ['id' => $page->id]));

        $this->setPageTitle($page->title);

        $this->dis['object'] = $page;

        return $this->themeController->handle($page, $this->dis);
    }
}
