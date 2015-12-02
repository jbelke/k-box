<?php namespace KlinkDMS\Http\Requests;

use KlinkDMS\Http\Requests\Request;
use Illuminate\Contracts\Auth\Guard;

class DocumentAddRequest extends Request {

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{

		$max_size = \Config::get('dms.max_upload_size');
		$supported_file_types = \Config::get('dms.allowed_file_types');

		//TODO: simply verify the extension and not the mime type 

		$tests = [
			'group' => 'sometimes|required|exists:groups,id', //when in the request the uploaded file is added to the group
			'document' => 'required|between:0.001,' . $max_size, // . '|mimes:' . $supported_file_types,
			'document_fullpath' => 'sometimes|required',
			'document_name' => 'sometimes|required',
			'folder_path' => 'sometimes|required|min:1',
		];

		return $tests;
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true; // would be great to handle user check here :)
	}

}