<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class AdvAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            '_link'=>'required|url',
            'category_id'=>'required',
            'image'=>'required',
            'priority'=>'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('advertisement_name_is_required'),
            '_link.required' => translate('advertisement_link_is_required'),
            '_link.url' => translate('advertisement_link_is_Wrang'),
            'image.required' => translate('advertisement_image_is_required'),
            'priority.required' => translate('advertisement_priority_is_required'),
            'category_id.required' => translate('advertisement_priority_is_required'),
        ];
    }

}
