<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Models\Category;
class CategoryService
{
    use FileManagerTrait;

    public function getAddData(object $request): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';

        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'slug' => Str::slug($request['name'][array_search('en', $request['lang'])]),
            'icon' => $this->upload('category/', 'webp', $request->file('image')),
            'icon_storage_type' => $request->has('image') ? $storage : null,
            'image_ad' => $this->upload('category/image_ad/', 'webp', $request->file('image-Ad')),
            'parent_id' => $request->get('parent_id', 0),
            'position' => $request['position'],
            'priority' => $request['priority'],
            'menu'=> isset($request['show_menu'])?true:false,
            'brands' => json_encode($request->input('brand', [])), // تحويل المصفوفة إلى JSON

        ];
    }

    public function getUpdateData(object $request, object $data): array
    {


        return [
            'name' => $request['name'][array_search('en', $request['lang'])],
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
