<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Models\Category;
class AdvService
{
    use FileManagerTrait;

    public function getAddData(object $request): array
    {

        return [
            'title' => $request['name'][array_search('en', $request['lang'])],
            'category' => $request['category_id'],
            'image' => $this->upload('Adv/', 'webp', $request->file('image')),
            'link' => $request['_link'],
            'status' => $request['status']??false,
            'priority' => $request['priority'],

        ];
    }

    public function getUpdateData(object $request, object $data): array
    {
        $image = $request->file('image') ? $this->update('Adv/', $data['image'], 'webp', $request->file('image')) : $data['icon'];


        return [
            'title' => $request['name'][array_search('en', $request['lang'])],
            'category' => $request['category_id'],
            'image' => $image,
            'link' => $request['_link'],
            'status' => $request['status']??false,
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
        if ($data->childes) {
            foreach ($data->childes as $child) {
                if ($child->childes) {
                    foreach ($child->childes as $item) {
                        if ($item['icon']) {
                            $this->delete('category/' . $item['icon']);
                        }
                    }
                }
                if ($child['icon']) {
                    $this->delete('category/' . $child['icon']);
                }
            }
        }
        if ($data['icon']) {
            $this->delete('category/' . $data['icon']);
        }
        return true;
    }
}
