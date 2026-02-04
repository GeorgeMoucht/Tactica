<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes','string','max:180'],
            'description' => ['sometimes','nullable','string'],

            'day_of_week' => ['sometimes','nullable','integer','between:1,7'],
            'starts_time' => ['sometimes','nullable','date_format:H:i'],
            'ends_time'   => ['sometimes','nullable','date_format:H:i'],

            'capacity'    => ['sometimes','nullable','integer','min:1'],
            'teacher_id'  => ['sometimes','nullable','integer','exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Only validate time order if BOTH keys are present in request.
            // (Because update can be partial and service merges with existing.)
            if ($this->has('starts_time') && $this->has('ends_time')) {
                $starts = $this->input('starts_time');
                $ends   = $this->input('ends_time');

                if ($starts && $ends && $ends <= $starts) {
                    $v->errors()->add('ends_time', 'End time must be after start time.');
                }
            }
        });
    }
}
