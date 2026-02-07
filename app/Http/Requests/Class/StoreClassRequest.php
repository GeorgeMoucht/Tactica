<?php

namespace App\Http\Requests\Class;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;


class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required','string','max:180'],
            'description' => ['nullable','string'],

            'type'        => ['sometimes','string','in:weekly,workshop'],
            'active'      => ['sometimes','boolean'],

            'day_of_week' => ['nullable','integer','between:1,7'],
            'starts_time' => ['nullable','date_format:H:i'],
            'ends_time'   => ['nullable','date_format:H:i'],

            'capacity'    => ['nullable','integer','min:1'],
            'teacher_id'  => ['nullable','integer','exists:users,id'],

            // Workshop sessions
            'sessions'               => ['nullable','array','min:1'],
            'sessions.*.date'        => ['required_with:sessions','date','after_or_equal:today'],
            'sessions.*.starts_time' => ['required_with:sessions','date_format:H:i'],
            'sessions.*.ends_time'   => ['required_with:sessions','date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $type = $this->input('type', 'weekly');

            if ($type === 'weekly') {
                $dow    = $this->input('day_of_week');
                $starts = $this->input('starts_time');
                $ends   = $this->input('ends_time');

                // If any schedule field exists, we need to require all.
                $anySchedule = ($dow !== null) || ($starts !== null) || ($ends !== null);

                if ($anySchedule) {
                    if (!$dow)    $v->errors()->add('day_of_week', 'day_of_week is required when schedule is provided.');
                    if (!$starts) $v->errors()->add('starts_time', 'starts_time is required when schedule is provided.');
                    if (!$ends)   $v->errors()->add('ends_time', 'ends_time is required when schedule is provided.');
                }

                if ($starts && $ends && $ends <= $starts) {
                    $v->errors()->add('ends_time', 'End time must be after start time.');
                }
            }

            if ($type === 'workshop') {
                $sessions = $this->input('sessions');

                if (empty($sessions) || !is_array($sessions)) {
                    $v->errors()->add('sessions', 'Sessions are required for workshops.');
                    return;
                }

                // Validate each session: ends_time > starts_time
                foreach ($sessions as $i => $session) {
                    $sStart = $session['starts_time'] ?? null;
                    $sEnd   = $session['ends_time'] ?? null;
                    if ($sStart && $sEnd && $sEnd <= $sStart) {
                        $v->errors()->add("sessions.$i.ends_time", 'Session end time must be after start time.');
                    }
                }

                // Unique dates
                $dates = array_column($sessions, 'date');
                if (count($dates) !== count(array_unique($dates))) {
                    $v->errors()->add('sessions', 'Session dates must be unique.');
                }
            }
        });
    }
}
