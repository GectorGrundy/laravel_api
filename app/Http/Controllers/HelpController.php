<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Help as R;
use App\Models\HelpQuestion;
use App\Models\HelpCategory;
use Auth;

class HelpController extends Controller
{
    use Helpers;

    /**
     * Instantiate a new HelpController instance.
     *
     */
    public function __construct()
    {

    }

    /**
     * Create help category
     *
     * @param R\CreateCategoryRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function createCategory(R\CreateCategoryRequest $request)
    {
        $help_category = HelpCategory::create($request->all());

        return $this->response->accepted(null, [
            'message' => trans('help.on_category_create_success'),
            'response' => $help_category,
            'status_code' => 202
        ]);
    }

    /**
     * Edit help category
     *
     * @param R\EditCategoryRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function editCategory(R\EditCategoryRequest $request)
    {
        $help_category = HelpCategory::find($request->input('category_id'));

        $help_category->update($request->all());

        return $this->response->accepted(null, [
            'message' => trans('help.on_category_edit_success'),
            'response' => $help_category,
            'status_code' => 202
        ]);
    }

    /**
     * Delete help category
     *
     * @param R\DeleteCategoryRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function deleteCategory(R\DeleteCategoryRequest $request)
    {
        HelpCategory::destroy($request->input('category_id'));

        return $this->response->accepted(null, [
            'message' => trans('help.on_category_remove_success'),
            'status_code' => 202
        ]);
    }

    /**
     * Create help question
     *
     * @param R\CreateQuestionRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function createQuestion(R\CreateQuestionRequest $request)
    {
        $help_question = HelpQuestion::create($request->all());

        return $this->response->accepted(null, [
            'message' => trans('help.on_question_create_success'),
            'response' => $help_question,
            'status_code' => 202
        ]);
    }

    /**
     * Edit help question
     *
     * @param R\EditQuestionRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function editQuestion(R\EditQuestionRequest $request)
    {
        $help_question = HelpQuestion::find($request->input('question_id'));

        $help_question->update($request->all());

        return $this->response->accepted(null, [
            'message' => trans('help.on_question_edit_success'),
            'response' => $help_question,
            'status_code' => 202
        ]);
    }

    /**
     * Remove help question
     *
     * @param R\DeleteQuestionRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function deleteQuestion(R\DeleteQuestionRequest $request)
    {
        HelpQuestion::destroy($request->input('question_id'));

        return $this->response->accepted(null, [
            'message' => trans('help.on_question_remove_success'),
            'status_code' => 202
        ]);
    }

    /**
     * Force remove help question (hard)
     *
     * @param R\ForceDeleteQuestionRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function forceDeleteQuestion(R\ForceDeleteQuestionRequest $request)
    {
        //get help question (include trashed)
        $help_question = HelpQuestion::where('id', $request->input('question_id'))->withTrashed()->first();

        //hard delete
        $help_question->forceDelete();

        return $this->response->accepted(null, [
            'message' => trans('help.on_question_force_remove_success'),
            'status_code' => 202
        ]);
    }

    /**
     * Restore question
     *
     * @param R\RestoreQuestionRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function restoreQuestion(R\RestoreQuestionRequest $request)
    {
        //get help question (include only trashed)
        $help_question = HelpQuestion::where('id', $request->input('question_id'))->onlyTrashed()->first();

        //restore
        $help_question->restore();

        return $this->response->accepted(null, [
            'message' => trans('help.on_question_restore_success'),
            'status_code' => 202
        ]);
    }

    /**
     * Get help tree
     *
     * @param R\GetTreeRequest $request
     * @return array|void
     */
    public function getTree(R\GetTreeRequest $request)
    {

        if (in_array(Auth::user()->role, ['administrator', 'manager'])) {
            $show_empty = $request->input('show_empty', false);
            $show_trashed = $request->input('show_trashed', false);
            $only_trashed = $request->input('only_trashed', false);
        } else {
            $show_empty = false;
            $show_trashed = false;
            $only_trashed = false;
        }

        $help_list = HelpCategory::with([
            'questions' => function ($query) use ($show_trashed, $only_trashed) {
                $query->ShowTrashed($show_trashed)->OnlyTrashed($only_trashed);
            }
        ])
            ->ShowEmpty($show_empty)
            ->get();

        return ['response' => $help_list, 'status_code' => 200];
    }


}
