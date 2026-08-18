<?php

namespace TwillRedirects\Twill\Capsules\Redirects\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class RedirectRequest extends Request
{
    public function rulesForCreate(): array
    {
        return $this->rules();
    }

    public function rulesForUpdate(): array
    {
        return $this->rules();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'redirects' => 'nullable|array',
            'redirects.*.from' => 'required_with:redirects|string',
            'redirects.*.to' => 'required_with:redirects|string',
            'redirects.*.statuscode' => 'nullable|integer|in:301,302,307,308',
        ];
    }
}
