<?php

namespace App\Http\Requests\ProductRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequestSavePiInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pi_type' => 'required|in:simple,advanced',
            'shipment_qty' => 'required|integer|min:0',
            'shipment_date' => 'nullable|date',
            'packing_note' => 'nullable|string|max:2000',
            'pi_rows' => 'nullable|array',
            'pi_rows.*.ordered_qty' => 'nullable|integer|min:0',
            'pi_rows.*.ctn_no' => 'nullable|string|max:100',
            'pi_rows.*.ctn_size' => 'nullable|string|max:100',
            'pi_rows.*.pcs_per_ctn' => 'nullable|integer|min:0',
            'pi_rows.*.ctn_qty' => 'nullable|integer|min:0',
            'pi_rows.*.total_pcs' => 'nullable|integer|min:0',
            'pi_rows.*.nw_kg' => 'nullable|numeric|min:0',
            'pi_rows.*.gw_kg' => 'nullable|numeric|min:0',
            'pi_rows.*.note' => 'nullable|string|max:500',
            'advanced_blocks' => 'nullable|array',
            'advanced_blocks.*.block_key' => 'nullable|string|max:100',
            'advanced_blocks.*.product_id' => 'nullable|integer',
            'advanced_blocks.*.title' => 'nullable|string|max:255',
            'advanced_blocks.*.color_label' => 'nullable|string|max:255',
            'advanced_blocks.*.image' => 'nullable|string|max:500',
            'advanced_blocks.*.variant_headers_csv' => 'nullable|string|max:1000',
            'advanced_blocks.*.color_headers_csv' => 'nullable|string|max:500',
            'advanced_blocks.*.size_headers_csv' => 'nullable|string|max:500',
            'advanced_blocks.*.rows' => 'nullable|array',
            'advanced_blocks.*.ctn_size' => 'nullable|string|max:100',
            'advanced_blocks.*.rows.*.ctn_qty' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.ctn_no' => 'nullable|string|max:100',
            'advanced_blocks.*.rows.*.variants' => 'nullable|array',
            'advanced_blocks.*.rows.*.variants.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.colors' => 'nullable|array',
            'advanced_blocks.*.rows.*.colors.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.sizes' => 'nullable|array',
            'advanced_blocks.*.rows.*.sizes.*' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.pcs' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.total_pcs' => 'nullable|integer|min:0',
            'advanced_blocks.*.rows.*.nw_kg' => 'nullable|numeric|min:0',
            'advanced_blocks.*.rows.*.gw_kg' => 'nullable|numeric|min:0',
        ];
    }
}
