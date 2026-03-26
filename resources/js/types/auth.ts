export interface User {
  id: number
  name: string
  email: string
  avatar: string
  timezone: string
  email_verified_at: string | null
  created_at: string
  updated_at: string

  [key: string]: unknown
}

export interface Auth {
  user: User
}

export interface TwoFactorConfigContent {
  title: string
  description: string
  buttonText: string
}
