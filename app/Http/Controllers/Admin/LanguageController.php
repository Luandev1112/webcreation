<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Language;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\File;
use Image;


class LanguageController extends Controller
{

    public function langManage($lang = false)
    {
        $page_title = 'Language Manager';
        $empty_message = 'No language has been added.';
        $languages = Language::orderByDesc('is_default')->get();
        $path = imagePath()['language']['path'];
        $size = imagePath()['language']['size'];
        return view('admin.language.lang', compact('page_title', 'empty_message', 'languages','path','size'));
    }

    public function langStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:languages',
            'text_align' => 'required|in:0,1',
            'icon' => ['nullable', 'image', new FileTypeValidate(['png'])]
        ]);



        $data = file_get_contents(resource_path('lang/') . 'en.json');
        $json_file = strtolower($request->code) . '.json';
        $path = resource_path('lang/') . $json_file;

        File::put($path, $data);

        $filename = null;
        if ($request->hasFile('icon')) {
            try {
                $path = imagePath()['language']['path'];
                $size = imagePath()['language']['size'];

                $filename = uploadImage($request->icon, $path, $size);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Could not upload icon.'];
                return back()->withNotify($notify);
            }
        }

        if ($request->is_default) {
            Language::where('is_default', 1)->update([
                'is_default' => 0
            ]);
        }
        Language::create([
            'name' => $request->name,
            'code' => strtolower($request->code),
            'text_align' => $request->text_align,
            'icon' => $filename,
            'is_default' => $request->is_default ? 1 : 0,
        ]);

        $notify[] = ['success', 'Create Successfully'];
        return back()->withNotify($notify);
    }

    public function langUpdatepp(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'text_align' => 'required|in:0,1',
            'icon' => ['nullable', 'image', new FileTypeValidate(['png'])]
        ]);

        $la = Language::findOrFail($id);

        $filename = $la->icon;
        if ($request->hasFile('icon')) {
            try {
                $path = imagePath()['language']['path'];
                $size = imagePath()['language']['size'];
                $filename = uploadImage($request->icon, $path, $size, $la->icon);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Could not upload icon.'];
                return back()->withNotify($notify);
            }
        }

        if ($request->is_default) {
            Language::where('is_default', 1)->update([
                'is_default' => 0
            ]);
        }


        $la->update([
            'name' => $request->name,
            'text_align' => $request->text_align,
            'icon' => $filename,
            'is_default' => $request->is_default ? 1 : 0,
        ]);

        $notify[] = ['success', 'Update Successfully'];
        return back()->withNotify($notify);
    }

    public function langDel($id)
    {
        $la = Language::find($id);
        removeFile(config('constants.language.path') . '/' . $la->icon);
        removeFile(resource_path('lang/') . $la->code . '.json');
        $la->delete();
        $notify[] = ['success', 'Delete Successfully'];
        return back()->withNotify($notify);
    }

    public function langEdit($id)
    {
        $la = Language::find($id);
        $page_title = "Update " . $la->name . " Keywords";
        $json = file_get_contents(resource_path('lang/') . $la->code . '.json');
        $list_lang = Language::all();


        if (empty($json)) {
            $notify[] = ['error', 'File Not Found.'];
            return back()->with($notify);
        }
        $json = json_decode($json);

        return view('admin.language.edit_lang', compact('page_title', 'json', 'la', 'list_lang'));
    }

    public function langUpdate(Request $request, $id)
    {
        $lang = Language::find($id);
        $content = json_encode($request->keys);

        if ($content === 'null') {
            $notify[] = ['error', 'At Least One Field Should Be Fill-up'];
            return back()->withNotify($notify);
        }
        file_put_contents(resource_path('lang/') . $lang->code . '.json', $content);
        $notify[] = ['success', 'Update Successfully'];
        return back()->withNotify($notify);
    }

    public function langImport(Request $request)
    {
        $mylang = Language::find($request->myLangid);
        $lang = Language::find($request->id);
        $json = file_get_contents(resource_path('lang/') . $lang->code . '.json');

        $json_arr = json_decode($json, true);

        file_put_contents(resource_path('lang/') . $mylang->code . '.json', json_encode($json_arr));

        return 'success';
    }
















    public function storeLanguageJson(Request $request, $id)
    {
        $la = Language::find($id);
        $this->validate($request, [
            'key' => 'required',
            'value' => 'required'
        ]);

        $items = file_get_contents(resource_path('lang/') . $la->code . '.json');

        $reqKey = trim($request->key);

        if (array_key_exists($reqKey, json_decode($items, true))) {
            $notify[] = ['error', "`$reqKey` Already Exist"];
            return back()->withNotify($notify);
        } else {
            $newArr[$reqKey] = trim($request->value);
            $itemsss = json_decode($items, true);
            $result = array_merge($itemsss, $newArr);
            file_put_contents(resource_path('lang/') . $la->code . '.json', json_encode($result));
            $notify[] = ['success', "`".trim($request->key)."` has been added"];
            return back()->withNotify($notify);
        }

    }
    public function deleteLanguageJson(Request $request, $id)
    {
        $this->validate($request, [
            'key' => 'required',
            'value' => 'required'
        ]);

        $reqkey = $request->key;
        $reqValue = $request->value;
        $lang = Language::find($id);
        $data = file_get_contents(resource_path('lang/') . $lang->code . '.json');

        $json_arr = json_decode($data, true);
        unset($json_arr[$reqkey]);

        file_put_contents(resource_path('lang/'). $lang->code . '.json', json_encode($json_arr));
        $notify[] = ['success', "`".trim($request->key)."` has been removed"];
        return back()->withNotify($notify);
    }
    public function updateLanguageJson(Request $request, $id)
    {
        $this->validate($request, [
            'key' => 'required',
            'value' => 'required'
        ]);

        $reqkey = trim($request->key);
        $reqValue = $request->value;
        $lang = Language::find($id);

        $data = file_get_contents(resource_path('lang/') . $lang->code . '.json');

        $json_arr = json_decode($data, true);

        $json_arr[$reqkey] = $reqValue;

        file_put_contents(resource_path('lang/'). $lang->code . '.json', json_encode($json_arr));

        $notify[] = ['success', "Update successfully"];
        return back()->withNotify($notify);
    }

}
