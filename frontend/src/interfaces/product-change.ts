import type { Product } from '@/interfaces/product'

export type ProductChange =
  | { type: 'created' | 'updated' | 'liked'; product: Product }
  | { type: 'deleted'; product: Pick<Product, 'id'> }
