-- Additional Audit Tables for Enhanced Audit Management

-- Main audit reports table
CREATE TABLE audit_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type ENUM('Internal', 'External', 'Compliance', 'Financial', 'Operational', 'Safety') NOT NULL,
    description TEXT,
    audit_date DATE NOT NULL,
    auditor_name VARCHAR(150),
    conducted_by INT NOT NULL, -- user_id of the auditor
    department ENUM('Finance', 'Operations', 'Sales', 'Inventory', 'HR', 'General') DEFAULT 'General',
    status ENUM('Planned', 'In Progress', 'Completed', 'Reviewed') NOT NULL DEFAULT 'Planned',
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    findings TEXT,
    recommendations TEXT,
    action_required ENUM('None', 'Minor', 'Major', 'Critical') DEFAULT 'None',
    deadline DATE NULL,
    assigned_to INT NULL, -- user_id responsible for follow-up
    completion_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conducted_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Audit findings/issues table
CREATE TABLE audit_findings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_report_id INT NOT NULL,
    finding_title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    category ENUM('Financial', 'Operational', 'Compliance', 'Safety', 'Security') NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    action_plan TEXT,
    assigned_to INT NULL,
    due_date DATE NULL,
    resolved_date DATE NULL,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (audit_report_id) REFERENCES audit_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Enhanced compliance violations table (if needed to extend the existing one)
CREATE TABLE compliance_violations_extended (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compliance_violation_id INT NOT NULL,
    corrective_action TEXT,
    preventive_measures TEXT,
    cost_impact DECIMAL(10,2) DEFAULT 0.00,
    business_impact ENUM('None', 'Low', 'Medium', 'High') DEFAULT 'Low',
    regulatory_body VARCHAR(100),
    reference_number VARCHAR(50),
    follow_up_date DATE,
    FOREIGN KEY (compliance_violation_id) REFERENCES compliance_violations(id) ON DELETE CASCADE
);

-- Audit checklist template
CREATE TABLE audit_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    audit_type ENUM('Internal', 'External', 'Compliance', 'Financial', 'Operational', 'Safety') NOT NULL,
    department ENUM('Finance', 'Operations', 'Sales', 'Inventory', 'HR', 'General') DEFAULT 'General',
    checklist_items JSON, -- Store checklist items as JSON
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);