import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import LoginView from '../LoginView.vue';
import { createPinia, setActivePinia } from 'pinia';

// Mocking vue-router agar tidak error saat komponen memanggil useRouter()
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
  })
}));

describe('LoginView.vue', () => {
  beforeEach(() => {
    // Inisialisasi Pinia sebelum setiap test berjalan
    setActivePinia(createPinia());
  });

  it('merender form login dan elemen penting dengan benar', () => {
    // Mount komponen
    const wrapper = mount(LoginView);

    // Verifikasi Title
    expect(wrapper.find('h1').text()).toContain('LeaveHub');

    // Verifikasi ketersediaan input Email dan Password
    expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    expect(wrapper.find('input[type="password"]').exists()).toBe(true);

    // Verifikasi tombol submit
    const submitButton = wrapper.find('button[type="submit"]');
    expect(submitButton.exists()).toBe(true);
    expect(submitButton.text()).toBe('Login');
  });
});
