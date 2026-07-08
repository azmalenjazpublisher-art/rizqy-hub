// supabase.js - إعدادات الاتصال بـ Supabase
const SUPABASE_URL = 'https://yzqahzwqwcxypryzarxk.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_qW1pvyy3MogDo5ZgK8JQQQ_w3mO0phv';

// إنشاء عميل Supabase
const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);