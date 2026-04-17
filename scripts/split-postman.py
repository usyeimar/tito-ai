#!/usr/bin/env python3
"""
Split a large Postman collection into modular files following the structure expected by merge-postman.py.

Usage:
    python scripts/split-postman.py --input postman-collection.json --output docs/postman

Structure created:
    docs/postman/
    ├── collection.postman.json          (base collection with info and variables)
    ├── tenant/
    │   ├── _index.postman.json          (folder metadata)
    │   ├── Activity/
    │   │   ├── _index.postman.json
    │   │   └── get.list-activity.postman.json
    │   └── Auth/
    │       ├── _index.postman.json
    │       └── post.tenant-refresh.postman.json
    └── central/                          (if central endpoints exist)
        └── ...
"""

import argparse
import json
import re
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple
from urllib.parse import urlsplit


# HTTP methods that can appear in endpoint filenames
HTTP_METHODS = {"get", "post", "put", "patch", "delete", "options", "head", "ws"}


def slugify(text: str) -> str:
    """Convert a name to a filesystem-friendly slug."""
    # Replace special characters and spaces with hyphens
    slug = re.sub(r"[^a-zA-Z0-9\s]", "-", text)
    slug = re.sub(r"\s+", "-", slug)
    slug = re.sub(r"-+", "-", slug)
    return slug.strip("-").lower()


def sanitize_filename(text: str, max_length: int = 50) -> str:
    """Create a safe filename from text."""
    slug = slugify(text)
    if len(slug) > max_length:
        slug = slug[:max_length]
    return slug


def get_path_tokens(url: Any) -> List[str]:
    """Extract URL path tokens."""
    if isinstance(url, dict):
        path = url.get("path", [])
        if isinstance(path, list):
            return [str(p).strip("/") for p in path if str(p).strip("/")]
        if isinstance(path, str):
            return [p for p in path.strip("/").split("/") if p]
        raw = url.get("raw", "")
        if isinstance(raw, str):
            return raw_path_tokens(raw)
    elif isinstance(url, str):
        return raw_path_tokens(url)
    return []


def raw_path_tokens(raw: str) -> List[str]:
    """Extract path tokens from raw URL."""
    raw = raw.strip()
    if not raw:
        return []
    without_query = raw.split("?", 1)[0]
    parsed = urlsplit(without_query)
    if parsed.path:
        return [p for p in parsed.path.strip("/").split("/") if p]
    # Handle Postman variable syntax like {{base_url}}/path
    if "}}" in without_query:
        suffix = without_query.split("}}", 1)[1]
        return [p for p in suffix.strip("/").split("/") if p]
    if without_query.startswith("/"):
        return [p for p in without_query.strip("/").split("/") if p]
    if "/" in without_query:
        suffix = without_query.split("/", 1)[1]
        return [p for p in suffix.strip("/").split("/") if p]
    return [without_query] if without_query else []


def determine_scope_from_path(path_tokens: List[str]) -> str:
    """
    Determine if endpoint belongs to 'tenant' or 'central' based on path.
    Tenant paths contain {tenant_slug} or similar patterns.
    """
    for token in path_tokens:
        token_lower = token.lower()
        if "tenant" in token_lower or "slug" in token_lower:
            return "tenant"
    return "central"


def get_http_method(item: dict) -> str:
    """Extract HTTP method from request item."""
    request = item.get("request", {})
    if isinstance(request, dict):
        method = request.get("method", "GET")
        return method.lower() if method else "get"
    return "get"


def extract_endpoint_leaf_name(item: dict) -> str:
    """Generate a leaf name for the endpoint file."""
    name = item.get("name", "")
    request = item.get("request", {})
    method = get_http_method(item)

    # Try to extract meaningful name from endpoint
    url_tokens = (
        get_path_tokens(request.get("url")) if isinstance(request, dict) else []
    )

    if name:
        # Use the request name, sanitized
        leaf = sanitize_filename(name, max_length=40)
    elif url_tokens:
        # Use last path segment
        leaf = sanitize_filename(url_tokens[-1], max_length=40)
    else:
        leaf = "request"

    return f"{method}.{leaf}"


def create_index_file(
    path: Path,
    name: str,
    order: Optional[int] = None,
    requests_order: Optional[int] = None,
) -> None:
    """Create an _index.postman.json file for a folder."""
    content: Dict[str, Any] = {"name": name}
    if order is not None:
        content["order"] = order
    if requests_order is not None:
        content["requests_order"] = requests_order

    index_path = path / "_index.postman.json"
    index_path.parent.mkdir(parents=True, exist_ok=True)
    index_path.write_text(json.dumps(content, indent=2))


def create_endpoint_file(path: Path, item: dict, order: Optional[int] = None) -> None:
    """Create an endpoint .postman.json file."""
    leaf_name = extract_endpoint_leaf_name(item)
    filename = f"{leaf_name}.postman.json"

    # Prepare the item structure (without nested items - endpoints don't have them)
    endpoint_item = {
        "name": item.get("name", ""),
        "request": item.get("request", {}),
    }

    # Add optional fields if present
    if "response" in item and item["response"]:
        endpoint_item["response"] = item["response"]
    if "event" in item and item["event"]:
        endpoint_item["event"] = item["event"]

    # Wrap in the structure expected by merge-postman.py
    content: Dict[str, Any] = {"item": endpoint_item}
    if order is not None:
        content["order"] = order

    endpoint_path = path / filename
    endpoint_path.write_text(json.dumps(content, indent=2))


def process_folder(
    folder: dict,
    base_path: Path,
    scope: str,
    parent_order: int = 1000,
    folder_depth: int = 0,
) -> Tuple[int, int]:
    """
    Process a folder and its contents recursively.

    Returns:
        Tuple of (files_created, folders_created)
    """
    folder_name = folder.get("name", f"Folder-{folder_depth}")
    items = folder.get("item", [])

    # Create slug for folder name
    folder_slug = sanitize_filename(folder_name, max_length=30)

    # Determine scope for this folder's contents
    # If this is the root level, determine scope from child items
    if folder_depth == 0:
        # Check first request to determine scope
        for item in items:
            if "request" in item:
                request = item.get("request", {})
                if isinstance(request, dict):
                    url_tokens = get_path_tokens(request.get("url"))
                    scope = determine_scope_from_path(url_tokens)
                    break

    # Build folder path
    folder_path = base_path / scope / folder_slug
    folder_path.mkdir(parents=True, exist_ok=True)

    # Create index file for this folder
    create_index_file(folder_path, folder_name, order=parent_order)
    folders_created = 1
    files_created = 0

    # Track order for endpoints
    endpoint_order = 10

    for item in items:
        if "item" in item:
            # This is a nested folder
            sub_files, sub_folders = process_folder(
                item,
                folder_path,
                scope,
                parent_order=endpoint_order,
                folder_depth=folder_depth + 1,
            )
            files_created += sub_files
            folders_created += sub_folders
        elif "request" in item:
            # This is an endpoint
            create_endpoint_file(folder_path, item, order=endpoint_order)
            files_created += 1
            endpoint_order += 10

    return files_created, folders_created


def split_collection(input_path: Path, output_dir: Path) -> Tuple[int, int, int]:
    """
    Split a Postman collection into modular files.

    Returns:
        Tuple of (endpoints_created, folders_created, variables_preserved)
    """
    # Load collection
    collection = json.loads(input_path.read_text())

    # Ensure output directory exists
    output_dir.mkdir(parents=True, exist_ok=True)

    # Extract base collection info
    base_collection = {
        "info": collection.get("info", {}),
    }

    # Preserve variables if present
    variables = collection.get("variable", [])
    if variables:
        base_collection["variable"] = variables

    # Save base collection
    base_path = output_dir / "collection.postman.json"
    base_path.write_text(json.dumps(base_collection, indent=2))

    # Process items
    items = collection.get("item", [])
    total_files = 0
    total_folders = 0

    for idx, item in enumerate(items):
        if "item" in item:
            # Top-level folder
            files, folders = process_folder(
                item,
                output_dir,
                scope="tenant",  # Default, will be determined from content
                parent_order=(idx + 1) * 10,
                folder_depth=0,
            )
            total_files += files
            total_folders += folders

    return total_files, total_folders, len(variables)


def main() -> None:
    parser = argparse.ArgumentParser(
        description="Split a large Postman collection into modular files"
    )
    parser.add_argument(
        "--input",
        default="postman-collection.json",
        help="Input Postman collection file",
    )
    parser.add_argument(
        "--output",
        default="docs/postman",
        help="Output directory for modular files",
    )
    parser.add_argument(
        "--report",
        action="store_true",
        help="Print split summary",
    )
    args = parser.parse_args()

    input_path = Path(args.input)
    output_dir = Path(args.output)

    if not input_path.exists():
        raise SystemExit(f"Input file not found: {input_path}")

    print(f"Splitting collection: {input_path}")
    print(f"Output directory: {output_dir}")
    print()

    endpoints, folders, variables = split_collection(input_path, output_dir)

    if args.report:
        print(f"endpoints_created={endpoints}")
        print(f"folders_created={folders}")
        print(f"variables_preserved={variables}")
    else:
        print(f"Created {endpoints} endpoint files")
        print(f"Created {folders} folder structures")
        print(f"Preserved {variables} collection variables")
        print()
        print(f"Collection split complete. You can now use:")
        print(
            f"  python scripts/merge-postman.py --source {output_dir} --output postman-collection.json"
        )


if __name__ == "__main__":
    main()
