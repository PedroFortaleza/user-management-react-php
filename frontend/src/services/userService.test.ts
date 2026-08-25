import { afterEach, describe, expect, it, vi } from 'vitest'

import { userService } from './userService'

describe('userService', () => {
  afterEach(() => vi.unstubAllGlobals())

  it('solicita a lista de usuários à API', async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response(JSON.stringify({ users: [] }), { status: 200 }))
    vi.stubGlobal('fetch', fetchMock)

    await expect(userService.list()).resolves.toEqual({ users: [] })
    expect(fetchMock).toHaveBeenCalledWith('http://localhost:8000/api/users', expect.objectContaining({ method: 'GET' }))
  })
})
