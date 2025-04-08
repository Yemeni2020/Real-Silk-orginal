<?php

namespace App\Repositories;

use App\Contracts\Repositories\AdvRepositoryInterface;
use App\Models\Translation;
use App\Models\Adv;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AdvRepository implements AdvRepositoryInterface
{
    public function __construct(
        private readonly Adv       $category,
        private readonly Translation    $translation
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->category->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->category->where($params)->with($relations)->withoutGlobalScopes()->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->category->with($relations)
                ->when(!empty($orderBy), function ($query) use ($orderBy) {
                    return $query->orderBy(array_key_first($orderBy),array_values($orderBy)[0]);
                });

        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit);
    }

    public function getListWhere(
        array $orderBy = [],
        string $searchValue = null,
        array $filters = [],
        array $relations = [],
        int|string $dataLimit = DEFAULT_DATA_LIMIT,
        int $offset = null
    ): Collection|LengthAwarePaginator {
        $query = $this->category->with($relations);
    
        // تطبيق الفلاتر
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value); // استخدام whereIn إذا كانت قيمة الفلتر مصفوفة
            } else {
                $query->where($field, $value);
            }
        }
    
        // تطبيق البحث
        $query->when(isset($searchValue), function ($query) use ($searchValue) {
            $translation_ids = $this->translation->where('translationable_type', 'App\Models\AdvCategory')
                ->where('key', 'name')
                ->where(function ($q) use ($searchValue) {
                    $q->orWhere('value', 'like', "%$searchValue%");
                })->pluck('translationable_id');
            $query->where('name', 'like', "%$searchValue%")->orWhereIn('id', $translation_ids);
        });
    
        // تطبيق الترتيب
        $query->when(!empty($orderBy), function ($query) use ($orderBy) {
            return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
        });
    
        $filters += ['searchValue' => $searchValue];
    
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    }

    // public function getListWhere(array $orderBy = [], string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    // {
    //     $query = $this->category->with($relations)
    //         ->where($filters)
    //         ->when(isset($searchValue), function ($query) use ($searchValue) {
    //             $translation_ids = $this->translation->where('translationable_type', 'App\Models\Category')
    //                 ->where('key', 'name')
    //                 ->where(function ($q) use ($searchValue) {
    //                     $q->orWhere('value', 'like', "%$searchValue%");
    //                 })->pluck('translationable_id');
    //             $query->where('name', 'like', "%$searchValue%")->orWhereIn('id', $translation_ids);
    //         })
    //         ->when(!empty($orderBy), function ($query) use ($orderBy) {
    //             return $query->orderBy(array_key_first($orderBy), array_values($orderBy)[0]);
    //         });

    //     $filters += ['searchValue' =>$searchValue];
    //     return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit)->appends($filters);
    // }
    
    
    public function update(string $id, array $data): bool
    {
        return $this->category->find($id)->update($data);
    }

    public function delete(array $params): bool
    {
        
        if(isset($params['id']))
            $this->category->where('id', $params['id'])->delete();
        elseif(isset($params['category']))
            $this->category->where('category', $params['category'])->delete();

        return true;
    }

}
