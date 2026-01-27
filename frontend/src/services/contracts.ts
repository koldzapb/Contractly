import api from './api'
import type { Contract, ApiResponse, PaginatedResponse } from '@/types'

export interface UploadContractData {
  file: File
  title?: string
}

export interface UpdateContractData {
  title: string
}

export interface ContractListParams {
  page?: number
  per_page?: number
}

export interface UploadProgressCallback {
  (progress: number): void
}

/**
 * Upload a new contract
 */
export async function uploadContract(
  data: UploadContractData,
  onProgress?: UploadProgressCallback,
): Promise<Contract> {
  const formData = new FormData()
  formData.append('file', data.file)
  if (data.title) {
    formData.append('title', data.title)
  }

  const response = await api.post<ApiResponse<Contract>>('/contracts', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    onUploadProgress: (progressEvent) => {
      if (onProgress && progressEvent.total) {
        const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        onProgress(progress)
      }
    },
  })

  return response.data.data
}

/**
 * Get paginated list of contracts
 */
export async function getContracts(
  params: ContractListParams = {},
): Promise<PaginatedResponse<Contract>> {
  const response = await api.get<PaginatedResponse<Contract>>('/contracts', {
    params: {
      page: params.page || 1,
      per_page: params.per_page || 15,
    },
  })

  return response.data
}

/**
 * Get a single contract by ID
 */
export async function getContract(id: string, withAnalysis = false): Promise<Contract> {
  const response = await api.get<ApiResponse<Contract>>(`/contracts/${id}`, {
    params: withAnalysis ? { with_analysis: true } : undefined,
  })

  return response.data.data
}

/**
 * Get contract status
 */
export async function getContractStatus(
  id: string,
): Promise<{ id: string; status: string; error_message?: string }> {
  const response = await api.get<
    ApiResponse<{ id: string; status: string; error_message?: string }>
  >(`/contracts/${id}/status`)

  return response.data.data
}

/**
 * Update a contract
 */
export async function updateContract(id: string, data: UpdateContractData): Promise<Contract> {
  const response = await api.put<ApiResponse<Contract>>(`/contracts/${id}`, data)

  return response.data.data
}

/**
 * Delete a contract
 */
export async function deleteContract(id: string): Promise<void> {
  await api.delete(`/contracts/${id}`)
}

/**
 * Validate file before upload
 */
export function validateFile(file: File): { valid: boolean; error?: string } {
  const maxSize = 10 * 1024 * 1024 // 10MB
  const allowedTypes = ['application/pdf']

  if (!allowedTypes.includes(file.type)) {
    return { valid: false, error: 'Only PDF files are allowed' }
  }

  if (file.size > maxSize) {
    return { valid: false, error: 'File size must be less than 10MB' }
  }

  return { valid: true }
}
