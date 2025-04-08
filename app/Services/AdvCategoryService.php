<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Models\Category;
class AdvCategoryService
{
    use FileManagerTrait;

    public function getAddData(object $request): array
    {

        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'icon' => $this->upload('AdvCategory/', 'webp', $request->file('image')),
            'priority' => $request['priority'],

        ];
    }

    public function getUpdateData(object $request, object $data): array
    {
        $image = $request->file('image') ? $this->update('AdvCategory/', $data['image'], 'webp', $request->file('image')) : $data['icon'];


        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'icon' => $image,
            'priority' => $request['priority'],

        ];
    }

    public function getSelectOptionHtml(object $data): string
    {
        $output = '<option value="" disabled selected>' . (translate('select_sub_category')) . '</option>';
        foreach ($data as $row) {
            $output .= '<option value="' . $row->id . '">' . $row->defaultName . '</option>';
        }
        return $output;
    }

    public function deleteImages(object $data): bool
    {
        if ($data->Adv) {
            foreach ($data->Adv as $child) {
                if ($child['image']) {
                    $this->delete('Adv/' . $child['image']);
                }
            }
        }
        if ($data['icon']) {
            $this->delete('category/' . $data['icon']);
        }
        return true;
    }
}
