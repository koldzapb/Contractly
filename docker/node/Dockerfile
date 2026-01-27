FROM node:20-alpine

# Install git (needed for some npm packages)
RUN apk add --no-cache git

# Set working directory
WORKDIR /app

# Expose Vite port
EXPOSE 5173

# Default command (overridden in docker-compose)
CMD ["npm", "run", "dev"]
