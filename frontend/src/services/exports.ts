import api from './api'

export type ExportFormat = 'pdf' | 'clauses' | 'deadlines' | 'all'

/**
 * Download a file from a blob response
 */
function downloadBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

/**
 * Get filename from Content-Disposition header
 */
function getFilenameFromHeader(header: string | null | undefined): string | null {
  if (!header) return null

  const match = header.match(/filename="?([^";\n]+)"?/)
  return match?.[1] ?? null
}

/**
 * Export contract as PDF report
 */
export async function exportPdf(contractId: string, contractTitle: string): Promise<void> {
  const response = await api.get(`/contracts/${contractId}/export/pdf`, {
    responseType: 'blob',
  })

  const filename =
    getFilenameFromHeader(response.headers['content-disposition']) ||
    `${contractTitle.replace(/[^a-zA-Z0-9_-]/g, '_')}_report.pdf`

  downloadBlob(response.data, filename)
}

/**
 * Export clauses as CSV
 */
export async function exportClausesCsv(contractId: string, contractTitle: string): Promise<void> {
  const response = await api.get(`/contracts/${contractId}/export/clauses`, {
    responseType: 'blob',
  })

  const filename =
    getFilenameFromHeader(response.headers['content-disposition']) ||
    `${contractTitle.replace(/[^a-zA-Z0-9_-]/g, '_')}_clauses.csv`

  downloadBlob(response.data, filename)
}

/**
 * Export deadlines as CSV
 */
export async function exportDeadlinesCsv(contractId: string, contractTitle: string): Promise<void> {
  const response = await api.get(`/contracts/${contractId}/export/deadlines`, {
    responseType: 'blob',
  })

  const filename =
    getFilenameFromHeader(response.headers['content-disposition']) ||
    `${contractTitle.replace(/[^a-zA-Z0-9_-]/g, '_')}_deadlines.csv`

  downloadBlob(response.data, filename)
}

/**
 * Export all data as ZIP
 */
export async function exportAllZip(contractId: string, contractTitle: string): Promise<void> {
  const response = await api.get(`/contracts/${contractId}/export/all`, {
    responseType: 'blob',
  })

  const filename =
    getFilenameFromHeader(response.headers['content-disposition']) ||
    `${contractTitle.replace(/[^a-zA-Z0-9_-]/g, '_')}_export.zip`

  downloadBlob(response.data, filename)
}
