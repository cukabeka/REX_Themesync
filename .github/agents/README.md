# REDAXO Themesync Developer Agent

This custom GitHub Copilot agent is specifically designed for developing the REDAXO Themesync addon with full context and best practices.

## Overview

The Themesync Developer Agent provides specialized knowledge for:
- REDAXO addon development patterns
- Themesync architecture and functionality
- PHP coding standards for REDAXO
- Database operations with rex_sql
- File and path management
- Synchronization logic and error handling

## Features

### REDAXO Context
- Deep knowledge of REDAXO 5.13+ architecture
- Understanding of addon structure and lifecycle
- Best practices for security, performance, and maintainability
- Proper use of REDAXO core classes (rex_sql, rex_config, rex_view, etc.)

### Themesync Specific Knowledge
- Repository pattern implementation (local filesystem, FTP)
- Module and template synchronization logic
- Bidirectional sync operations
- Configuration management via repo.yml
- Asset handling and path resolution

### Development Guidelines
- Coding standards and naming conventions
- Error handling with rex_exception
- Internationalization with rex_i18n
- Logging with rex_logger
- CSRF protection and permission checks

## Usage

### In VS Code with GitHub Copilot
1. Open the Themesync addon directory
2. Start a conversation with the agent
3. Ask questions about REDAXO development or Themesync functionality

### Example Queries
- "How do I implement a new repository type for Themesync?"
- "What's the best way to handle sync conflicts in REDAXO?"
- "How should I structure error handling for FTP operations?"
- "What are the REDAXO best practices for database operations?"
- "How do I add internationalization to a new feature?"

## Architecture Understanding

### Core Components
- **Repositories**: Different sync sources (local, FTP, future: GitHub)
- **Managers**: Module and template CRUD operations
- **Items**: Base classes for modules/templates with sync metadata
- **Sources**: Data source abstraction layer

### Key Classes
- `rex_themesync_repo_localfilesystem` - Local file sync
- `rex_themesync_repo_ftp` - FTP-based synchronization
- `rex_themesync_module_manager` - Module operations
- `rex_themesync_template_manager` - Template operations
- `rex_themesync_item_base` - Common functionality

## Development Workflow

1. **Planning**: Use agent to understand REDAXO patterns
2. **Implementation**: Get code suggestions with proper REDAXO integration
3. **Review**: Ask for code review with REDAXO best practices
4. **Testing**: Get guidance on testing approaches
5. **Documentation**: Generate proper documentation

## Integration with REDAXO

The agent understands:
- REDAXO's MVC-like architecture
- Addon lifecycle (install/update/uninstall)
- Backend page structure
- Fragment system for reusable components
- Configuration management
- Permission system
- Internationalization

## File Structure Knowledge

```
/redaxo/src/addons/themesync/
├── .github/agents/themesync-developer.json  # This agent
├── lib/                                     # Core classes
├── pages/                                   # Backend pages
├── lang/                                    # Translations
├── assets/                                  # Static resources
├── boot.php                                # Initialization
├── package.yml                             # Metadata
└── README.md                               # Documentation
```

## Best Practices Enforced

- **Security**: CSRF protection, input validation, permission checks
- **Performance**: Efficient database queries, caching where appropriate
- **Maintainability**: Clean code, proper documentation, error handling
- **Compatibility**: Backward compatibility, cross-platform support
- **User Experience**: Proper error messages, loading states, feedback

## Troubleshooting

If the agent doesn't understand a specific REDAXO concept:
1. Check the main REDAXO documentation
2. Look at existing Themesync code for patterns
3. Ask specific questions about the unclear area
4. Reference the REDAXO API documentation

## Contributing

When updating this agent:
1. Keep knowledge current with REDAXO developments
2. Add new patterns as they're discovered
3. Update for new Themesync features
4. Maintain compatibility with GitHub Copilot standards

## Related Resources

- [REDAXO Documentation](https://redaxo.org/doku/main)
- [REDAXO API](https://redaxo.org/api/main/)
- [FriendsOfREDAXO Community](https://friendsofredaxo.github.io/)
- [Themesync GitHub Repository](https://github.com/cukabeka/REX_Themesync)