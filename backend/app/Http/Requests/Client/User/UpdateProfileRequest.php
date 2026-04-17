<?php

namespace App\Http\Requests\Client\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Tên: 2-50 ký tự, CHỈ CHỨA CHỮ CÁI VÀ KHOẢNG TRẮNG (Hỗ trợ tiếng Việt \pL)
            'full_name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s]+$/u'],
            
            // SĐT: Bỏ trống hoặc đúng 10 số bắt đầu bằng số 0, không trùng với người khác
            'phone' => 'nullable|string|regex:/^0[0-9]{9}$/|unique:users,phone,' . $this->user()->id,
            
            'gender' => 'nullable|string|in:Nam,Nữ,Khác,male,female,other',
            
            // Ngày sinh: Phải trước ngày hôm nay và sau năm 1900 (không ai sống quá 120 tuổi)
            'birthday' => 'nullable|date|before:today|after:1900-01-01',
            
            // Chiều cao: 50cm -> 250cm
            'height_cm' => 'nullable|integer|min:50|max:250',
            
            // Cân nặng: 10kg -> 300kg
            'weight_kg' => 'nullable|numeric|min:10|max:300',

            // XÁC THỰC ẢNH AVATAR
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Tối đa 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.min' => 'Tên quá ngắn, vui lòng nhập ít nhất 2 ký tự.',
            'full_name.max' => 'Tên quá dài, tối đa 50 ký tự.',
            'full_name.regex' => 'Họ và tên không được chứa số hoặc ký tự đặc biệt (@,#,$,...).',
            
            'phone.regex' => 'Số điện thoại không hợp lệ (phải là 10 số và bắt đầu bằng số 0).',
            'phone.unique' => 'Số điện thoại này đã được sử dụng cho tài khoản khác.',
            
            'birthday.before' => 'Ngày sinh không thể là ngày trong tương lai.',
            'birthday.after' => 'Năm sinh không hợp lệ (phải sau 1900).',
            
            'height_cm.min' => 'Chiều cao không hợp lệ (Tối thiểu 50cm).',
            'height_cm.max' => 'Chiều cao không hợp lệ (Tối đa 250cm).',
            
            'weight_kg.min' => 'Cân nặng không hợp lệ (Tối thiểu 10kg).',
            'weight_kg.max' => 'Cân nặng không hợp lệ (Tối đa 300kg).',

            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Định dạng ảnh không hỗ trợ (chỉ nhận jpeg, png, jpg, webp).',
            'avatar.max' => 'Dung lượng ảnh không được vượt quá 5MB.',
        ];
    }
}