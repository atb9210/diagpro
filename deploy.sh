A#!/bin/bash

# Diagpro Docker Deployment Script
# This script builds and deploys the Diagpro application using Docker

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="diagpro"
DOCKER_REGISTRY="registry.digitalocean.com"
REGISTRY_NAME="diagpro-registry"
IMAGE_NAME="$REGISTRY_NAME/$APP_NAME"
VERSION=${1:-"latest"}
FULL_IMAGE_NAME="$DOCKER_REGISTRY/$IMAGE_NAME:$VERSION"

echo -e "${BLUE}🚀 Diagpro Docker Deployment${NC}"
echo -e "${BLUE}================================${NC}"
echo -e "Image: ${GREEN}$FULL_IMAGE_NAME${NC}"
echo -e "Version: ${GREEN}$VERSION${NC}"
echo ""

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
echo -e "${YELLOW}🔍 Checking prerequisites...${NC}"
if ! command_exists docker; then
    echo -e "${RED}❌ Docker is not installed${NC}"
    exit 1
fi

if ! command_exists doctl; then
    echo -e "${RED}❌ DigitalOcean CLI (doctl) is not installed${NC}"
    echo -e "${YELLOW}💡 Install with: snap install doctl${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Prerequisites check passed${NC}"

# Login to DigitalOcean Container Registry
echo -e "${YELLOW}🔐 Logging into DigitalOcean Container Registry...${NC}"
doctl registry login

# Build the Docker image
echo -e "${YELLOW}🏗️ Building Docker image...${NC}"
docker build -t $FULL_IMAGE_NAME .

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Docker image built successfully${NC}"
else
    echo -e "${RED}❌ Docker image build failed${NC}"
    exit 1
fi

# Push the image to registry
echo -e "${YELLOW}📤 Pushing image to registry...${NC}"
docker push $FULL_IMAGE_NAME

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Image pushed successfully${NC}"
else
    echo -e "${RED}❌ Image push failed${NC}"
    exit 1
fi

# Tag as latest if version is not latest
if [ "$VERSION" != "latest" ]; then
    echo -e "${YELLOW}🏷️ Tagging as latest...${NC}"
    LATEST_IMAGE="$DOCKER_REGISTRY/$IMAGE_NAME:latest"
    docker tag $FULL_IMAGE_NAME $LATEST_IMAGE
    docker push $LATEST_IMAGE
    echo -e "${GREEN}✅ Latest tag pushed${NC}"
fi

# Clean up local images (optional)
read -p "$(echo -e ${YELLOW}🧹 Clean up local images? [y/N]: ${NC})" -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker rmi $FULL_IMAGE_NAME
    if [ "$VERSION" != "latest" ]; then
        docker rmi $LATEST_IMAGE 2>/dev/null || true
    fi
    echo -e "${GREEN}✅ Local images cleaned up${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}Image: ${GREEN}$FULL_IMAGE_NAME${NC}"
echo -e "${BLUE}Registry: ${GREEN}$DOCKER_REGISTRY${NC}"
echo ""
echo -e "${YELLOW}💡 To deploy on DigitalOcean App Platform:${NC}"
echo -e "   1. Create a new app from container image"
echo -e "   2. Use image: ${GREEN}$FULL_IMAGE_NAME${NC}"
echo -e "   3. Set environment variables from .env.docker"
echo -e "   4. Add MySQL and Redis databases"
echo ""