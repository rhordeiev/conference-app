<?php

namespace App\Http\Controllers;

use App\Helpers\ConferenceHelper;
use App\Helpers\ReportHelper;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CategoryController extends Controller
{
    public static function all(): Collection
    {
        return Category::all();
    }

    public function create(CategoryRequest $request)
    {
        $category = new Category($request->validated());
        $category->save();
    }

    public function remove(Request $request)
    {
        Category::getCategoryById($request->get('id'))->delete();
    }

    public function getCategoryById(int $id)
    {
        return Category::getCategoryById($id);
    }

    public function edit(CategoryRequest $request)
    {
        Category::getCategoryById($request->get('id'))->update(
            $request->validated()
        );
    }

    public function attachCategoryToConference(Request $request)
    {
        $category = Category::getCategoryById($request->category_id);
        $category->categoryConferences()->attach($request->conference_id);
    }

    public function detachCategoryFromConference(Request $request)
    {
        $category = Category::getCategoryById($request->category_id);
        $category->categoryConferences()->detach($request->conference_id);
    }

    public function attachCategoryToReport(Request $request)
    {
        $category = Category::getCategoryById($request->category_id);
        $category->categoryReports()->attach($request->report_id);
    }

    public function detachReportsByConferenceId(Request $request)
    {
        $categories = Category::getChildCategories($request->parent_id)->pluck(
            'id'
        );
        $reports = Report::getReportsByConference($request->conference_id)
            ->pluck('id');
        foreach ($categories as $category) {
            foreach ($reports as $report) {
                $info = [
                    'category_id' => $category,
                    'report_id' => $report,
                ];
                $this->detachCategoryFromReport($request, $info);
            }
        }
    }

    public function getChildCategories(int $conferenceId): Collection
    {
        $conference = Conference::getConferenceById($conferenceId);
        $parentCategory = $conference->getCategory();

        return $parentCategory?->id ? Category::getChildCategories(
            $parentCategory->id
        ) : new Collection();
    }

    public function detachCategoryFromReport(
        Request $request,
        array $info = null
    ) {
        $categoryId = $info ? $info['category_id'] : $request->category_id;
        $reportId = $info ? $info['report_id'] : $request->report_id;
        $category = Category::getCategoryById($categoryId);
        $category->categoryReports()->detach($reportId);
    }

    public function getCategoryConferences(Request $request, int $id)
    {
        $category = Category::getCategoryById($id);
        $conferences = $category->getCategoryConferences();
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token->tokenable;
            if ($conferences->count() !== 0 && $user) {
                if ($user->type === 'listener') {
                    ConferenceHelper::addFieldToConference($conferences, $user, 'joined');
                } else {
                    if ($user->type === 'announcer') {
                        ConferenceHelper::addFieldToConference($conferences, $user, 'creator');
                    }
                }
            }
        }

        return $conferences !== 0 ? $conferences : [];
    }

    public function getCategoryReports(Request $request, int $id)
    {
        $category = Category::getCategoryById($id);
        $reports = $category->getCategoryReports();
        if ($request->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            $user = $token->tokenable;
            foreach ($reports as $report) {
                $report['favorite'] = ReportHelper::addFavoriteFieldToReport(
                    $user['id'],
                    $report
                );
            }
        }

        return $reports->count() !== 0 ? $reports : [];
    }
}
